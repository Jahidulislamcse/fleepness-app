<?php

namespace App\Support\Notification\Contracts;

use Illuminate\Notifications\AnonymousNotifiable;

interface SupportsFcmTopicChannel extends SupportsFcmChannel
{
    public function toFcmTopic(AnonymousNotifiable|FcmNotifiableByTopic $notifiable): string;
}
