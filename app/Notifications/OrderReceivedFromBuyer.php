<?php

namespace App\Notifications;

use App\Enums\SellerOrderStatus;
use App\Models\SellerOrder;
use App\Support\Notification\Channels\FcmDeviceChannel;
use App\Support\Notification\Contracts\FcmNotifiable;
use App\Support\Notification\Contracts\SupportsFcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;

class OrderReceivedFromBuyer extends Notification implements ShouldQueue, SupportsFcmChannel
{
    use Queueable;

    public function __construct(public SellerOrder $sellerOrder)
    {
        $this->afterCommit();
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        return $this->sellerOrder->status === SellerOrderStatus::Pending;
    }

    public function toFcm(FcmNotifiable $notifiable): CloudMessage
    {
        return CloudMessage::new()
            ->withNotification([
                'title' => 'New Order Received',
                'body' => 'You have received a new order from a buyer.',
            ])
            ->withData([
                'total_amount' => (string) $this->sellerOrder->product_cost,
            ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [FcmDeviceChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
