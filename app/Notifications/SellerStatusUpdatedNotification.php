<?php

namespace App\Notifications;

use App\Enums\SellerStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use App\Support\Notification\Contracts\SupportsSmsChannel;

class SellerStatusUpdatedNotification extends Notification implements ShouldBroadcast, ShouldQueueAfterCommit, SupportsSmsChannel
{
    use Queueable;

    public function __construct(public readonly SellerStatus $status) {}

    public static function approved()
    {
        return new self(SellerStatus::Approved);
    }

    public static function rejected()
    {
        return new self(SellerStatus::Rejected);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['sms', 'broadcast'];
    }

    public function toSms(object $notifiable): string
    {
        return $this->status->messageBody();
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toBroadcast(object $notifiable)
    {
        return [
            'notifiable' => $notifiable,
            'notification' => [
                'title' => $this->status->messageTitle(),
                'body' => $this->status->messageBody(),
            ],
        ];
    }

    public function broadcastAs()
    {
        return 'seller_status_updated';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->status->messageTitle(),
            'body' => $this->status->messageBody(),
        ];
    }
}
