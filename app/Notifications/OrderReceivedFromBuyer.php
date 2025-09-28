<?php

namespace App\Notifications;

use App\Models\SellerOrder;
use Illuminate\Bus\Queueable;
use App\Enums\SellerOrderStatus;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Notifications\AnonymousNotifiable;
use App\Support\Notification\Contracts\FcmNotifiable;
use App\Support\Notification\Channels\FcmDeviceChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Support\Notification\Contracts\SupportsFcmChannel;

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

    public function toFcm(AnonymousNotifiable|FcmNotifiable $notifiable): CloudMessage
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
            // 'broadcast',
        ];
    }

    // /**
    //  * Get the channels the event should broadcast on.
    //  *
    //  * @return array
    //  */
    // public function broadcastOn()
    // {
    //     return [
    //         new PrivateChannel('channel-name'),
    //     ];
    // }

    // public function toBroadcast(object $notifiable): BroadcastMessage
    // {
    //     return new BroadcastMessage([
    //         'id' => 'hahahah',
    //     ]);
    // }

    // /**
    //  * The event's broadcast name.
    //  */
    // public function broadcastAs(): string
    // {
    //     return 'order-received-from-buyer';
    // }

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
