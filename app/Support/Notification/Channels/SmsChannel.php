<?php

namespace App\Support\Notification\Channels;

use Illuminate\Support\Arr;
use App\Support\Sms\Facades\Sms;
use Illuminate\Notifications\Notification;
use Illuminate\Container\Attributes\Singleton;
use App\Support\Notification\Contracts\SupportsSmsChannel;

#[Singleton]
class SmsChannel
{
    public function send($notifiable, Notification&SupportsSmsChannel $notification): void
    {
        /** @var string|string[]|null */
        $mobiles = $notifiable->routeNotificationFor('sms', $notification);
        $mobiles = Arr::wrap($mobiles);
        if (empty($mobiles)) {
            return;
        }

        $message = $notification->toSms($notifiable);

        Sms::withMessage($message)->withMobiles($mobiles)->send();
    }
}
