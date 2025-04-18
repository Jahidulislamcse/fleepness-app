<?php

namespace App\Facades;

use App\Services\PathaoService;
use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Services\PathaoService
 */
class Pathao extends Facade
{
    const DELIVERY_TYPE_NORMAL = 48;

    const DELIVERY_TYPE_ON_DEMAND = 12;

    const ITEM_TYPE_PARCEL = 2;

    const ITEM_TYPE_DOCUMENT = 1;

    protected static function getFacadeAccessor(): string
    {
        return PathaoService::class;
    }
}
