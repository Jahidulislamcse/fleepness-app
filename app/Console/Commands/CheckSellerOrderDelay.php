<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SellerOrder;
use App\Enums\SellerOrderStatus;
use Carbon\Carbon;

class CheckSellerOrderDelay extends Command
{
    protected $signature = 'orders:check-delay';
    protected $description = 'Mark seller orders as delayed if delivery end time has passed';

    public function handle()
    {
        $now = Carbon::now();

        $orders = SellerOrder::where('status', '!=', SellerOrderStatus::Delivered)
            ->whereNotNull('delivery_end_time')     
            ->where('delivery_end_time', '<', $now) 
            ->where('is_delay', 0)                  
            ->get();

        foreach ($orders as $order) {
            $order->is_delay = 1;
            $order->save();
        }

        $this->info($orders->count() . ' seller orders marked as delayed.');
    }
}
