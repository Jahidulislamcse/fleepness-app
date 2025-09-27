<?php

namespace App\Support\Notification\Contracts;

use Illuminate\Notifications\AnonymousNotifiable;

interface SupportsFcmTopicChannel extends SupportsFcmChannel
{
    public function toFcmTopic(FcmNotifiable|AnonymousNotifiable $notifiable): string;
}
