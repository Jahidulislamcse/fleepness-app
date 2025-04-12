<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Http\Controllers\SMSController;

class OTPAuthController extends Controller
{
    // Register user and send OTP via SMS
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|digits_between:10,15|unique:users',
            // 'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generate OTP
        $otp = rand(1000, 9999);
        $otp_expiry = Carbon::now()->addMinutes(10);

        // Create user but mark as unverified
        $user = User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            // 'password' => Hash::make($request->password),
            'otp' => $otp,
            'otp_expires_at' => $otp_expiry,
        ]);

        // Send OTP via SMS using SMSController
        $smsController = new SMSController();
        $smsController->sendSMS(new Request([
            'Message' => "Your OTP is: $otp",
            'MobileNumbers' => $user->phone_number,
        ]));

        return response()->json([
            'message' => 'OTP sent to your phone_number.',
            'user_id' => $user->id
        ]);
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|digits:4'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);

        if (!$user || $user->otp !== $request->otp || Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }

        // OTP verified, clear it and authenticate user
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'OTP verified successfully.',
            'token' => $user->createToken('auth_token')->plainTextToken
        ]);
    }

    // Resend OTP
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);

        // Generate new OTP
        $otp = rand(100000, 999999);
        $otp_expiry = Carbon::now()->addMinutes(10);

        $user->otp = $otp;
        $user->otp_expires_at = $otp_expiry;
        $user->save();

        // Send OTP via SMS
        $smsController = new SMSController();
        $smsController->sendSMS(new Request([
            'Message' => "Your new OTP is: $otp",
            'MobileNumbers' => $user->phone_number,
        ]));

        return response()->json(['message' => 'New OTP sent to your phone_number.']);
    }
}
