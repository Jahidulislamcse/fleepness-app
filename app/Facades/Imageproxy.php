<?php

namespace App\Facades;

use App\Services\ImageproxyService;
use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Services\ImageproxyService
 */
class Imageproxy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ImageproxyService::class;
    }
}
