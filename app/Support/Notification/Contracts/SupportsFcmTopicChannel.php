<?php

namespace App\Support\Notification\Contracts;

interface SupportsFcmTopicChannel extends SupportsFcmChannel
{
    /**
     * @return string|string[]|null
     */
    public function toFcmTopics(object $notifiable);
}
