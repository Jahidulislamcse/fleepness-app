<?php

namespace App\Services;

use App\Support\Sms\SmsApiConnector;
use Illuminate\Container\Attributes\Singleton;

#[Singleton]
class SMSService
{
    public function __construct(
        private readonly SmsApiConnector $smsApiConnector
    ) {}

    /**
     * Send a single SMS
     *
     * @param  string  $message
     * @param  string  $mobileNumber
     * @return array
     */
    public function sendSMS($message, $mobileNumber)
    {
        $payload = [
            'Message' => $message,
            'MobileNumbers' => $mobileNumber,
        ];

        // Log the payload before sending the request
        \Log::info('Sending SMS Payload:', $payload);

        $response = $this->smsApiConnector->sendSms($payload);

        // Log the response for debugging
        \Log::info('SMS API Response:', $response->json());

        return $response->json();
    }

    /**
     * Send bulk SMS
     *
     * @return list<string>
     */
    public function sendBulkSMS(array $messages)
    {
        $payload = [
            'MessageParameters' => $messages,
        ];

        $response = $this->smsApiConnector->sendBulkSMS($payload);

        return $response->json();
    }
}
