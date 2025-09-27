<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Stringable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class SMSController extends Controller
{
    public function sendSMS(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'Message' => ['required', 'string'],
            'MobileNumbers' => ['required', 'string'],  // Comma-separated mobile numbers
            'ScheduleTime' => ['sometimes', Rule::date()->format('Y-m-d H:i')],
        ]);

        $validated = $validator->validate();

        // Ensure the number starts with '880'
        $mobileNumber = $validator
            ->safe()
            ->str('MobileNumbers')
            ->trim()
            ->pipe(function (Stringable $str) {
                return $str->when($str->startsWith('0'))->prepend('88');
            })
            ->pipe(function (Stringable $str) {
                return $str->unless($str->startsWith('880'))->prepend('880');
            })
            ->value();

        // Update the validated data with the formatted number
        $validated['MobileNumbers'] = $mobileNumber;

        // Send the request to the SMS API
        $response = Http::sms()->sendSms([
            'Message' => $validated['Message'],
            'MobileNumbers' => $validated['MobileNumbers'],
            'Is_Unicode' => true,
            'Is_Flash' => false,
            'DataCoding' => '8',
            'ScheduleTime' => $validated['ScheduleTime'] ?? null,
            'GroupId' => '',
        ]);

        // Check for a successful response
        if ($response->successful()) {
            return response()->json([
                'ErrorCode' => 0,
                'ErrorDescription' => 'Success',
                'Data' => $response->json()['Data'] ?? [],
            ]);
        } else {
            return response()->json([
                'ErrorCode' => $response->status(),
                'ErrorDescription' => $response->body(),
            ], $response->status());
        }
    }
}
