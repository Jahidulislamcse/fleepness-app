<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use App\Support\Notification\Contracts\SupportsSmsChannel;

class SmsNotification extends Notification implements ShouldQueueAfterCommit, SupportsSmsChannel
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly string $message) {}

    public function toSms(object $notifiable): string
    {
        return $this->message;
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
