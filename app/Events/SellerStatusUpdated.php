<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class SellerStatusUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public $sellerId;
    public $message;

    public function __construct($sellerId, $message)
    {
        $this->sellerId = $sellerId;
        $this->message = $message;

        // Store notification in the database
        Notification::create([
            'user_id' => $this->sellerId,
            'message' => $this->message,
            'status' => 'unread'
        ]);
    }

    public function broadcastOn()
    {
        return new Channel('seller-status-' . $this->sellerId);
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message
        ];
    }
}
