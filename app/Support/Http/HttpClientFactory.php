<?php

namespace App\Support\Http;

use Override;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Client\Factory;

class HttpClientFactory extends Factory
{
    #[Override]
    public function createPendingRequest()
    {
        return parent::createPendingRequest()
            ->setHandler(resolve(HandlerStack::class));
    }
}
