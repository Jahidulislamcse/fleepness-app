<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\SMSController;
use Illuminate\Support\Facades\Validator;

class OTPAuthController extends Controller
{
    // Register user and send OTP via SMS
    public function register(Request $request): JsonResponse
    {
        $rules = [
            'phone_number' => 'required|digits:11|unique:users,phone_number',
            'name' => 'nullable|string|max:255',
        ];

        $messages = [
            'phone_number.digits' => 'Invalid number. Phone number must be exactly 11 digits.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if (
                $validator->errors()->has('phone_number') &&
                str_contains($validator->errors()->first('phone_number'), 'Invalid number')
            ) {
                return response()->json(['message' => $validator->errors()->first('phone_number')], 400);
            }

            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
        ]);

        $otp = rand(1000, 9999);

        cache()->put('otp_'.$user->phone_number, $otp, now()->addMinutes(10));

        (new SMSController)->sendSMS(new Request([
            'Message' => "Your OTP is: $otp",
            'MobileNumbers' => $user->phone_number,
        ]));

        return response()->json([
            'message' => 'OTP sent to your phone_number.',
            'user_id' => $user->id,
            'phone_number' => $user->phone_number,
            'otp' => $otp,
        ]);
    }

    // Verify OTP
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|exists:users,phone_number',
            'otp' => 'required|digits:4',
        ]);

        $validator->validate();

        $user = User::where('phone_number', $request->phone_number)->first();

        $cachedOtp = cache()->get('otp_'.$user->phone_number);
        if (! $cachedOtp) {
            return response()->json(['message' => 'OTP expired.'], 400);
        }

        if ((string) $cachedOtp !== (string) $request->otp) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        // OTP verified, remove OTP from cache
        cache()->forget('otp_'.$user->phone_number);

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
            'phone_number' => 'required|string|exists:users,phone_number',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('phone_number', $request->phone_number)->first();

        // Generate a new OTP
        $otp = rand(1000, 9999);
        // dd($otp);

        // Store the new OTP in cache
        cache()->put('otp_'.$user->phone_number, $otp, now()->addMinutes(10));

        // Send new OTP via SMS
        $smsController = new SMSController;
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
