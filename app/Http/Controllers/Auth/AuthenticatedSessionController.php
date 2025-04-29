<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SMSController;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Illuminate\Http\JsonResponse;

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

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function storeapi(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function apiSendOtp(Request $request): JsonResponse
    {
        // Validate the phone number
        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
        ]);

        $phone = $validated['phone'];

        // Check if the phone number exists in the database
        $user = User::where('phone_number', $phone)->first();

        if (!$user) {
            // If the phone number does not exist in the database
            return response()->json([
                'message' => 'Phone number is not registered.',
                'status' => false,
            ], 404);
        }

        // Generate OTP
        $otp = rand(1000, 9999);

        // Store OTP in cache for 5 minutes
        Cache::put('otp_' . $phone, $otp, now()->addMinutes(5));

        // Format the phone number (e.g., add country code '880' for Bangladesh)
        $mobileNumber = trim($phone);
        if (str_starts_with($mobileNumber, '0')) {
            $mobileNumber = '880' . substr($mobileNumber, 1); // If it starts with '0', prepend '880'
        } elseif (!str_starts_with($mobileNumber, '880')) {
            $mobileNumber = '880' . $mobileNumber; // Add '880' if it doesn't already have it
        }

        // Create a new instance of SMSController
        $smsController = new SMSController();

        // Create the request to send OTP
        $smsRequest = new Request([
            'Message' => "Your OTP is: {$otp}",
            'MobileNumbers' => $mobileNumber,
        ]);

        // Call the sendSMS function from SMSController
        $smsResponse = $smsController->sendSMS($smsRequest);

        // Retrieve response data
        $smsResponseData = $smsResponse->getData(true);

        // If the SMS API returns an error, send failure response
        if (($smsResponseData['ErrorCode'] ?? 1) !== 0) {
            return response()->json([
                'message' => 'Failed to send OTP.',
                'status' => false,
                'details' => $smsResponseData['ErrorDescription'] ?? 'Unknown error',
            ], 500);
        }

        // If OTP sent successfully, return success response
        return response()->json([
            'message' => 'OTP sent successfully.',
            'status' => true,
        ]);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the user by phone number
        $user = User::where('phone_number', $request->phone_number)->first();

        // If user doesn't exist, return an error
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Retrieve the OTP from the cache
        $cacheOtp = Cache::get('otp_' . $user->phone_number);  // Cache key using phone_number

        // Check if OTP exists in cache
        if (!$cacheOtp) {
            return response()->json(['message' => 'OTP has expired or is not set'], 400);
        }

        // Check if OTP matches with the cached value
        if ((string) $cacheOtp !== (string) $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        // OTP is valid, clear it from cache to prevent reuse
        Cache::forget('otp_' . $user->phone_number);

        // Generate and return an access token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'OTP verified successfully.',
            'token' => $token
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

        return redirect('/');
    }

    public function destroyapi(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Revoke all tokens for the user
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }
}
