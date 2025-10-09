<?php

namespace App\Support\Notification\Contracts;

interface SupportsFcmDeviceChannel extends SupportsFcmChannel
{
    /**
     * @return string|string[]|null
     */
    public function toFcmTokens(object $notifiable);
}
