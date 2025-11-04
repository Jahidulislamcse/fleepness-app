<?php

namespace App\Support\Sms\Facades;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;
use App\Support\Sms\Factory\SmsProviderFactory;

/**
 * @mixin SmsProviderFactory
 *
 * @method static self withMessage(string $message) Set the SMS message content
 * @method static self withBulk(list<array{number:string,text:string}> $bulkMessage) Set bulk messages
 * @method static self withMobile(string $mobile) Set a single recipient mobile number
 * @method static self withMobiles(list<string> $mobiles) Set multiple recipient mobile numbers
 * @method static self withScheduleTime(null|Carbon|string $scheduleTime) Set a scheduled sending time
 * @method static self addMobile(string $mobile) Add a mobile number to the recipients
 * @method static array<string,mixed> send() Send the SMS to the configured recipient(s)
 * @method static array<string,mixed> sendBulk() Send the SMS to multiple recipients in bulk
 */
class Sms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SmsProviderFactory::class;
    }
}
