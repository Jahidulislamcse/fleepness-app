<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Support\Sms\Facades\Sms;
use Illuminate\Support\Stringable;
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
        $response = Sms::withMessage($validated['Message'])
            ->withScheduleTime($validated['ScheduleTime'] ?? null)
            ->withMobiles(
                str($validated['MobileNumbers'])
                    ->explode(',')
                    ->map(str(...))
                    ->map->trim()
                    ->map->value()
                    ->all()
            )
            ->send();

        return response()->json($response);
    }
}
