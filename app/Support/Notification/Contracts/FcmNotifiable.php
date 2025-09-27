<?php

namespace App\Support\Notification\Contracts;

use Illuminate\Notifications\Notification;

interface FcmNotifiable
{
    /**
     * @return list<string>|string|null
     */
    public function routeNotificationForFcmTokens(Notification&SupportsFcmChannel $notification): array|string|null;
}
