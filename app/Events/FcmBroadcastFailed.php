<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use App\Support\Notification\Contracts\FcmBroadcastNotifiableByDevice;

class FcmBroadcastFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public FcmBroadcastNotifiableByDevice $notifiable,
        public string $event,
        public string $channel,
        public array $data = [],
    ) {}

}
