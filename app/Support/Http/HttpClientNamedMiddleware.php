<?php

namespace App\Support\Http;

use Closure;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;

class HttpClientNamedMiddleware
{
    private bool $silent = false;

    /**
     * @param Closure(callable $handler,PendingRequest):(Closure(RequestInterface,array):PromiseInterface) $fn
     */
    public function __construct(
        public readonly string $name,
        private readonly PendingRequest $pendingRequest,
        private readonly Closure $fn,
    ) {}

    /**
     * @return Closure(RequestInterface,array):PromiseInterface
     */
    public function __invoke(callable $handler): Closure
    {
        if ($this->silent) {
            return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
                return $handler($request, $options);
            };
        }

        return $this->fn->__invoke($handler, $this->pendingRequest);
    }

    public function disable(): self
    {
        $this->silent = true;

        return $this;
    }

    public function enable(): self
    {
        $this->silent = false;

        return $this;
    }
}
