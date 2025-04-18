<?php

namespace App\Facades;

use App\Data\Dto\GeneratePublisherTokenData;
use App\Data\Dto\GenerateSubscriberTokenData;
use App\Services\LivestreamService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string generatePublisherToken(GeneratePublisherTokenData $data)
 * @method static string generateSubscriberToken(GenerateSubscriberTokenData $data)
 * @see \App\Services\LivestreamService
 * @mixin \App\Services\LivestreamService
 */
class Livestream extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LivestreamService::class;
    }
}
