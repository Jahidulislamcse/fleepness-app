<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Stringable;
use App\Notifications\SmsNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

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
        $scheduleTime = $validator->safe()->date('ScheduleTime');

        // Ensure the number starts with '880'
        $mobileNumbers = $validator
            ->safe()
            ->str('MobileNumbers')
            ->explode(',')
            ->map(str(...))
            ->map->trim()
            ->map->whenStartsWith('0', function (Stringable $str) {
                return $str->prepend('88');
            })
            ->map->whenDoesntStartWith('880', function (Stringable $str) {
                return $str->prepend('880');
            })
            ->map->value()
            ->all();

        $smsNotification = (new SmsNotification($validated['Message']));

        if ($scheduleTime) {
            $smsNotification->delay($scheduleTime);
        }

        Notification::route('sms', $mobileNumbers)->notify($smsNotification);

        return response()->json([
            'message' => Response::$statusTexts[Response::HTTP_ACCEPTED],
        ], Response::HTTP_ACCEPTED);
    }
}
