<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\SMSController;

class OTPAuthController extends Controller
{
    // Register user and send OTP via SMS
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|digits_between:10,15|unique:users',
            'name' => 'required|string|max:255',
            // 'password' => 'required|min:6', // If needed later
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create the user but do not store OTP in database
        $user = User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            // 'password' => Hash::make($request->password),
        ]);

        // Generate OTP
        $otp = rand(1000, 9999);

        // Store OTP in cache with expiry time (e.g., 10 minutes)
        Cache::put('register_otp_' . $user->id, $otp, now()->addMinutes(10));

        // Send OTP via SMS
        $smsController = new SMSController();
        $smsController->sendSMS(new Request([
            'Message' => "Your OTP is: $otp",
            'MobileNumbers' => $user->phone_number,
        ]));

        return response()->json([
            'message' => 'OTP sent to your phone_number.',
            'user_id' => $user->id,
        ]);
    }

    // Verify OTP
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|digits:4'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);

        $cachedOtp = Cache::get('register_otp_' . $user->id);

        if (!$cachedOtp) {
            return response()->json(['message' => 'OTP expired.'], 400);
        }

        if ((string) $cachedOtp !== (string) $request->otp) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        // OTP verified, remove OTP from cache
        Cache::forget('register_otp_' . $user->id);

        // Create and return a token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'OTP verified successfully.',
            'token' => $token,
        ]);
    }


    // Resend OTP
    public function resendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);

        // Generate a new OTP
        $otp = rand(1000, 9999);
        // dd($otp);

        // Store the new OTP in cache
        Cache::put('register_otp_' . $user->id, $otp, now()->addMinutes(10));

        // Send new OTP via SMS
        $smsController = new SMSController();
        $smsController->sendSMS(new Request([
            'Message' => "Your new OTP is: $otp",
            'MobileNumbers' => $user->phone_number,
        ]));

        return response()->json([
            'message' => 'New OTP sent to your phone_number.',
            'otp' => $otp,
        ]);
    }
}
