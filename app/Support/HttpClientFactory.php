<?php

namespace App\Support;

use Override;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Client\Factory;
use Illuminate\Container\Attributes\Singleton;

#[Singleton]
class HttpClientFactory extends Factory
{
    #[Override]
    public function createPendingRequest()
    {
        return parent::createPendingRequest()
            ->setHandler(resolve(HandlerStack::class));
    }
}
