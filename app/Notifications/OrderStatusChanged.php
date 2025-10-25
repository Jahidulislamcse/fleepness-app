<?php

namespace App\Notifications;

use App\Models\SellerOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OrderStatusChanged extends Notification implements ShouldBroadcast
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly SellerOrder $sellerOrder)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toBroadcast(object $notifiable)
    {
        return [
            'notifiable' => $notifiable,

            'order_code' => $this->sellerOrder->order->order_code,

            'status' => $this->sellerOrder->status->value,
            'status_message' => $this->sellerOrder->status_message,
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
            'order_code' => $this->sellerOrder->order->order_code,

            'status' => $this->sellerOrder->status->value,
            'status_message' => $this->sellerOrder->status_message,
        ];
    }
}
