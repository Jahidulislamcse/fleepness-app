<?php

namespace App\Support\Notification\Contracts;

interface SupportsFcmTopicChannel extends SupportsFcmChannel
{
    /**
     * @param  \Illuminate\Notifications\Notifiable  $notifiable
     */
    public function toFcmTopic(mixed $notifiable): string;
}
