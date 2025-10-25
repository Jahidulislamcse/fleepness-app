<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use App\Support\Notification\Contracts\SupportsSmsChannel;

class LoginOtpNotification extends Notification implements ShouldQueueAfterCommit, SupportsSmsChannel
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly int|string $otp) {}

    public function toSms(object $notifiable): string
    {
        return "Your OTP is: {$this->otp}";
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['sms'];
    }
}
