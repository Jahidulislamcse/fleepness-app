<?php

namespace App\Services;

use App\Support\Sms\Facades\Sms;
use Illuminate\Container\Attributes\Singleton;

#[Singleton]
class SMSService
{
    /**
     * Send a single SMS
     *
     * @param  string  $message
     * @param  string  $mobileNumber
     * @return array
     */
    public function sendSMS($message, $mobileNumber)
    {
        // Log the payload before sending the request
        \Log::info('Sending SMS Payload:', compact('message', 'mobileNumber'));

        $result = Sms::withMessage($message)->withMobile($mobileNumber)->send();

        // Log the response for debugging
        \Log::info('SMS API Response:', $result);

        return $result;
    }

    /**
     * Send bulk SMS
     *
     * @param  list<array{number:string,text:string}>  $messages
     * @return array<string,mixed>
     */
    public function sendBulkSMS(array $messages)
    {
        $result = Sms::withBulk($messages)->sendBulk();

        return $result;
    }
}
