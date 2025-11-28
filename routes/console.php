<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('orders:check-delay')
    ->everyMinute()
    ->sendOutputTo(storage_path('logs/orders-delay.log'));
