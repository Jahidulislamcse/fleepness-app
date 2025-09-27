<?php

namespace App\Support\Notification\Contracts;

interface SupportsFcmTopicChannel extends SupportsFcmChannel
{
    public function toFcmTopic(FcmNotifiable $notifiable): string;
}
