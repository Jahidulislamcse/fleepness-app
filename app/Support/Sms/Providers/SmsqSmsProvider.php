<?php

namespace App\Support\Sms\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Container\Attributes\Singleton;

#[Singleton]
class SmsqSmsProvider extends BaseProvider
{
    public function send(string $message, array|string $mobiles, null|Carbon|string $scheduleTime): array
    {
        $mobiles = Arr::wrap($mobiles);

        $response = Http::sms()->sendSms([
            'Message' => $message,
            'MobileNumbers' => Arr::join($mobiles, ','),
            'Is_Unicode' => true,
            'Is_Flash' => false,
            'DataCoding' => '8',
            'ScheduleTime' => $scheduleTime,
            'GroupId' => '',
        ])->throw();

        return $response->json();
    }

    public function sendBulk(array $bulkMessage, null|Carbon|string $scheduleTime): array
    {
        $messageParameters = collect($bulkMessage)->map(function ($entry) {
            return [
                'Text' => $entry['text'],
                'Number' => $entry['number'],
            ];
        })->all();

        // Send the request to the SMS API
        $response = Http::sms()->sendBulkSMS([
            'Is_Unicode' => true,
            'Is_Flash' => false,
            'DataCoding' => '8',
            'ScheduleTime' => $scheduleTime,
            'MessageParameters' => $messageParameters,
        ])->throw();

        return $response->json();
    }
}
