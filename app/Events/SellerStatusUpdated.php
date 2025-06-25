<?php

namespace App\Events;

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
    }

    public function broadcastOn()
    {
        return new Channel('seller-status-' . $this->sellerId);
    }

    public function broadcastWith()
    {
        \Log::info("Broadcasting to seller-status-{$this->sellerId}", ['message' => $this->message]);
        return ['message' => $this->message];
    }

    public function broadcastAs()
    {
        return 'SellerStatusUpdated';
    }
}
