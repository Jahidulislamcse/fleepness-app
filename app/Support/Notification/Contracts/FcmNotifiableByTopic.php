<?php

namespace App\Support\Notification\Contracts;

use Illuminate\Notifications\Notification;

interface FcmNotifiableByTopic
{
    /**
     * @return list<string>|string|null
     */
    public function routeNotificationForFcmTopics(Notification&SupportsFcmChannel $notification): null|array|string;
}
