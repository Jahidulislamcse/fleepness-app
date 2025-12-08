<?php

namespace App\Console\Commands;

use App\Models\SellerOrder;
use Illuminate\Console\Command;
use App\Enums\SellerOrderStatus;

class CheckSellerOrderDelay extends Command
{
    protected $signature = 'orders:check-delay';

    protected $description = 'Mark seller orders as delayed if delivery end time has passed';

    public function handle()
    {
        $count = SellerOrder::where('status', '!=', SellerOrderStatus::Delivered)
            ->whereNotNull('delivery_end_time')
            ->where('delivery_end_time', '<', now())
            ->where('is_delay', 0)
            ->update(['is_delay' => 1]);

        $this->info("$count seller orders marked as delayed.");
    }
}
