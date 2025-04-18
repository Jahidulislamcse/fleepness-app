<?php

namespace App\Facades;

use App\Services\SslCommerzService;
use Illuminate\Support\Facades\Facade;

class SSLCommerz extends Facade
{
    public static function getFacadeAccessor()
    {
        return SslCommerzService::class;
    }
}
