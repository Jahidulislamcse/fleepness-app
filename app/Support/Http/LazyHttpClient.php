<?php

namespace App\Support\Http;

use Traversable;
use IteratorAggregate;
use BadMethodCallException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * @see Factory
 * @see PendingRequest
 *
 * @mixin Factory
 * @mixin PendingRequest
 */
class LazyHttpClient implements IteratorAggregate
{
    use ForwardsCalls;

    public private(set) ?string $key = null;

    /** @var array<int, array{string, array}> */
    private array $callStack = [];

    public function __construct(protected null|Factory|PendingRequest $asyncPendingRequest = null) {}

    public function __call(string $name, array $arguments): static
    {
        $httpFacadeRoot = Http::getFacadeRoot();
        // Determine the target class/object for method existence check
        $target = $this->asyncPendingRequest ?? $httpFacadeRoot;
        $targetClass = is_object($target) ? $target::class : $target;

        $hasMethod = method_exists(PendingRequest::class, $name) || method_exists($httpFacadeRoot::class, $name);
        $hasMacro = PendingRequest::hasMacro($name) || $httpFacadeRoot::hasMacro($name);

        throw_if(! $hasMethod && ! $hasMacro, BadMethodCallException::class, "Method {$targetClass}::{$name}() does not exist.");

        $this->callStack[] = [$name, $arguments];

        return $this;
    }

    public function as(string $key)
    {
        $this->key = $key;

        return $this;
    }

    public function getIterator(): Traversable
    {
        // Instantiate the underlying async client (deferred)
        $client = $this->asyncPendingRequest?->async() ?? Http::async();

        // Replay deferred calls
        foreach ($this->callStack as [$method, $args]) {
            $client = $this->forwardCallTo($client, $method, $args);
        }

        $this->callStack = [];

        yield $this->key => $client;
    }
}
