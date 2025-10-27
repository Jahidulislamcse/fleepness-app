<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Container\Attributes\CurrentUser;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended('/admin/dashboard');
    }

    public function storeapi(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function apiSendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'digits:11'],
        ], [
            'phone.digits' => 'Phone number must be exactly 11 digits.',
            'phone.required' => 'Phone number is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first('phone'),
            ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $phone = $request->input('phone');

        // Check if the phone number exists in the database
        $user = User::where('phone_number', $phone)->first();

        if (! $user) {
            // If the phone number does not exist in the database
            return response()->json([
                'status' => false,
                'is_exist' => false,
                'message' => 'Phone number is not registered.',
            ], HttpResponse::HTTP_NOT_FOUND);
        }

        // Generate OTP
        $otp = Str::otp();

        // Store OTP in cache
        $user->cacheOtpFor10Minutes($otp);

        $user->sendOtpNotification($otp);

        // If OTP sent successfully, return success response
        return response()->json([
            'status' => true,
            'message' => 'OTP sent to your phone.',
            'otp' => ! app()->isProduction() ? $otp : null,
        ], HttpResponse::HTTP_ACCEPTED);
    }

    public function verifyCacheOtp(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|exists:users,phone_number',
            'otp' => 'required|digits:4',
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Find the user by phone number
        $user = User::where('phone_number', $request->phone_number)->first();

        // If user doesn't exist, return an error
        if (! $user) {
            return response()->json(['message' => 'User not found'], HttpResponse::HTTP_NOT_FOUND);
        }

        // Retrieve the OTP from the cache
        $cacheOtp = $user->getCachedOtp();  // Cache key using phone_number

        // Check if OTP exists in cache
        if (! $cacheOtp) {
            return response()->json(['message' => 'OTP has expired or is not set'], 400);
        }

        // Check if OTP matches with the cached value
        if ((string) $cacheOtp !== (string) $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        // OTP is valid, clear it from cache to prevent reuse
        $user->forgetCachedOtp();

        // Generate and return an access token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'OTP verified successfully.',
            'token' => $token,
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function destroyapi(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        // Revoke all tokens for the user
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    public function storeDeviceToken(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string',
        ]);

        $validator->validate();

        $deviceToken = $validator->safe()->str('device_token')->value();

        $user->deviceTokens()->firstOrCreate([
            'token' => $deviceToken,
        ]);

        return Response::json([
            'message' => 'Device token stored successfully',
        ]);
    }
}
