<?php

namespace App\Support\Concurrency\Drivers;

use Closure;
use Laravel\Octane\Facades\Octane;
use Illuminate\Contracts\Concurrency\Driver;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Defer\DeferredCallback;

use function Illuminate\Support\defer;

#[Singleton]
class OctaneDriver implements Driver
{
    private int $waitMilliseconds {
        get => data_get($this->config, 'wait_milliseconds', 3_000);
    }

        /**
         * Create a new class instance.
         */
        public function __construct(
            private readonly array $config
        ) {}

    public function run(array|Closure $tasks): array
    {
        return Octane::concurrently($tasks, $this->waitMilliseconds);
    }

    public function defer(array|Closure $tasks): DeferredCallback
    {
        return defer(fn () => $this->run($tasks));
    }
}
