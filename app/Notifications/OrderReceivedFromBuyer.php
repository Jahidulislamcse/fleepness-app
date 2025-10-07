<?php

namespace App\Notifications;

use App\Models\SellerOrder;
use Illuminate\Bus\Queueable;
use App\Enums\SellerOrderStatus;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Support\Notification\Contracts\SupportsFcmDeviceChannel;

class OrderReceivedFromBuyer extends Notification implements ShouldQueue, SupportsFcmDeviceChannel
{
    use Queueable;

    public function __construct(public SellerOrder $sellerOrder)
    {
        $this->afterCommit();
    }

    public function toFcmTokens(object $notifiable)
    {
        return $notifiable->routeNotificationForFcmTokens($this);
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        return SellerOrderStatus::Pending === $this->sellerOrder->status;
    }

    public function databaseType(): string
    {
        return 'order_received_from_buyer';
    }

    public function toFcm(object $notifiable): CloudMessage
    {
        return CloudMessage::new()
            ->withNotification([
                'title' => 'New Order Received',
                'body' => 'You have received a new order from a buyer.',
            ])
            ->withData($this->buildNotificationData());
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            'fcm-device',
            'database',
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function buildNotificationData(): array
    {
        return [
            'buyer' => $this->sellerOrder->order->user->name,
            'total_amount' => (string) $this->sellerOrder->product_cost,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Order Received',
            'body' => 'You have received a new order from a buyer.',
            ...$this->buildNotificationData(),
        ];
    }
}
