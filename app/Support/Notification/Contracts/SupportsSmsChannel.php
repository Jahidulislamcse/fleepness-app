<?php

namespace App\Support\Notification\Contracts;

interface SupportsSmsChannel
{
    public function toSms(object $notifiable): string;
}
