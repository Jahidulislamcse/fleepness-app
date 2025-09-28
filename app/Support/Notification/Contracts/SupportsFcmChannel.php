<?php

namespace App\Support\Notification\Contracts;

use Illuminate\Notifications\AnonymousNotifiable;

interface SupportsFcmChannel
{
    public function toFcm(AnonymousNotifiable|FcmNotifiableByDevice|FcmNotifiableByTopic $notifiable): \Kreait\Firebase\Messaging\CloudMessage;
}
