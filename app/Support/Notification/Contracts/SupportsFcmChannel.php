<?php

namespace App\Support\Notification\Contracts;

use Illuminate\Notifications\AnonymousNotifiable;

interface SupportsFcmChannel
{
    public function toFcm(FcmNotifiable|AnonymousNotifiable $notifiable): \Kreait\Firebase\Messaging\CloudMessage;
}
