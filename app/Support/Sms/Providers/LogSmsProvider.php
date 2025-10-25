<?php

namespace App\Support\Sms\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Container\Attributes\Singleton;

#[Singleton]
class LogSmsProvider extends BaseProvider
{
    public function send(string $message, array|string $mobiles, null|Carbon|string $scheduleTime): array
    {
        Log::info('sending message', compact('message', 'mobiles', 'scheduleTime'));

        return [
            'ErrorCode' => 0,
            'ErrorDescription' => 'Success',
            'Data' => [
                [
                    'MobileNumber' => '7894561230',
                    'MessageId' => '5c0780c2-d6f2-40a8-a40a-ee74e28fa4c2',
                ],
            ],
        ];
    }

    public function sendBulk(array $bulkMessage, null|Carbon|string $scheduleTime): array
    {
        Log::info('sending bulk message', compact('bulkMessage', 'scheduleTime'));

        return [
            'ErrorCode' => 0,
            'ErrorDescription' => 'Success',
            'Data' => [
                [
                    'MobileNumber' => '7894561230',
                    'MessageId' => '5c0780c2-d6f2-40a8-a40a-ee74e28fa4c2',
                ],
            ],
        ];
    }
}
