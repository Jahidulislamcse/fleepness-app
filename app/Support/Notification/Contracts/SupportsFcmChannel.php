<?php

namespace App\Support\Notification\Contracts;

interface SupportsFcmChannel
{
    public function toFcm(FcmNotifiable $notifiable): \Kreait\Firebase\Messaging\CloudMessage;
}
