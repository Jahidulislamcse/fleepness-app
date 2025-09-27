<?php

namespace App\Support;

use GuzzleHttp\HandlerStack;
use Illuminate\Http\Client\Factory;
use Override;

class HttpClientFactory extends Factory
{
    #[Override]
    public function createPendingRequest()
    {
        return parent::createPendingRequest()
            ->setHandler(resolve(HandlerStack::class));
    }
}
