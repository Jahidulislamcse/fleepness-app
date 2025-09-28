<?php

namespace App\Notifications;

use App\Models\SellerOrder;
use Illuminate\Bus\Queueable;
use App\Enums\SellerOrderStatus;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Notifications\AnonymousNotifiable;
use App\Support\Notification\Channels\FcmDeviceChannel;
use App\Support\Notification\Contracts\SupportsFcmChannel;
use App\Support\Notification\Contracts\FcmNotifiableByTopic;
use App\Support\Notification\Contracts\FcmNotifiableByDevice;

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
        return SellerOrderStatus::Pending === $this->sellerOrder->status;
    }

    public function toFcm(AnonymousNotifiable|FcmNotifiableByDevice|FcmNotifiableByTopic $notifiable): CloudMessage
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
        return [
            FcmDeviceChannel::class,
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
            //
        ];
    }
}
