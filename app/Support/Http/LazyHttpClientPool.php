<?php

namespace App\Support\Http;

use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @see LazyHttpClient
 *
 * @mixin LazyHttpClient
 */
class LazyHttpClientPool
{
    use ForwardsCalls;

    public private(set) array $lazyClients = [];

    public function __call($name, $arguments)
    {
        return $this->lazyClients[] = $this->forwardCallTo(resolve(LazyHttpClient::class), $name, $arguments);
    }
}
