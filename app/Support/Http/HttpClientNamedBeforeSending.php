<?php

namespace App\Support\Http;

use Closure;
use Psr\Http\Message\RequestInterface;
use Illuminate\Http\Client\PendingRequest;

class HttpClientNamedBeforeSending
{
    /**
     * @param Closure(\Illuminate\Http\Client\Request,array,PendingRequest):(\Illuminate\Http\Client\Request|RequestInterface) $fn
     */
    public function __construct(
        public readonly string $name,
        private readonly Closure $fn
    ) {}

    public function __invoke(\Illuminate\Http\Client\Request $request, array $options, PendingRequest $client): null|\Illuminate\Http\Client\Request|RequestInterface
    {
        return $this->fn->__invoke($request, $options, $client);
    }
}
