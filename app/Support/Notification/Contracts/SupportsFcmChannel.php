<?php

namespace App\Support\Notification\Contracts;

interface SupportsFcmChannel
{
    public function toFcm(object $notifiable): \Kreait\Firebase\Messaging\CloudMessage;
}
