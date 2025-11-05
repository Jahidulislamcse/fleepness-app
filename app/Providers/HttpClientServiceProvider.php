<?php

namespace App\Providers;

use Closure;
use Exception;
use Generator;
use GuzzleHttp\Utils;
use Psr\Log\LogLevel;
use GuzzleHttp\Promise;
use GuzzleHttp\Middleware;
use Illuminate\Support\Str;
use League\Uri\UriTemplate;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\MessageFormatter;
use Cerbero\JsonParser\JsonParser;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Illuminate\Http\Client\Factory;
use App\Support\Http\LazyHttpClient;
use App\Support\Sms\SmsApiConnector;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\RequestInterface;
use App\Support\Http\HttpClientFactory;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ResponseInterface;
use App\Support\Http\LazyHttpClientPool;
use GuzzleHttp\Promise\PromiseInterface;
use Spatie\Activitylog\Facades\LogBatch;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Database\Eloquent\Casts\Json;
use App\Support\Http\HttpClientNamedMiddleware;
use App\Support\Http\HttpClientNamedBeforeSending;

class HttpClientServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(Factory::class, HttpClientFactory::class);
        $this->app->singleton(function (): \GuzzleHttp\HandlerStack {
            $stack = new HandlerStack;

            return tap($stack)->setHandler(Utils::chooseHandler());
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Http::globalOptions([
            // RequestOptions::STREAM => ! app()->isLocal(),
            RequestOptions::DEBUG => app()->isLocal()
                ? fopen(
                    storage_path('logs/http-client.log'),
                    'a'
                )
                : false,
        ]);

        if (! app()->environment('testing')) {
            Http::globalMiddleware(
                Middleware::log(
                    logs('stderr'),
                    new MessageFormatter('{hostname} {req_header_User-Agent} - [{date_common_log}] "{method} {target} HTTP/{version}" {response}\n--------\n{error}'),
                    LogLevel::INFO
                )
            );
        }

        Http::globalRequestMiddleware(static function (RequestInterface $request) {
            return LogBatch::withinBatch(static function ($reqTraceId) use ($request): \Psr\Http\Message\MessageInterface {
                return $request->withHeader(
                    'x-trace-id',
                    $reqTraceId
                );
            });
        });

        Http::globalResponseMiddleware(static function (ResponseInterface $response) {
            return LogBatch::withinBatch(static function ($reqTraceId) use ($response): \Psr\Http\Message\MessageInterface {
                return $response->withHeader(
                    'x-trace-id',
                    $reqTraceId
                );
            });
        });

        Response::macro('lazyJson', function (): JsonParser {
            return JsonParser::parse($this);
        });

        PendingRequest::macro('sms', function (): SmsApiConnector {
            return resolve(SmsApiConnector::class, [
                'client' => $this,
            ]);
        });

        PendingRequest::macro('debugRequest', function (?callable $onRequest = null, bool $die = false): PendingRequest {
            /** @var PendingRequest $this */

            /** @var HandlerStack $handlerStack */
            $handlerStack = resolve(HandlerStack::class);
            $handlerStack->remove('debugRequestMiddleware');

            /** @var PendingRequest $this */
            $onRequestNonNull = $onRequest ?? static function (RequestInterface $request, array $options): void {
                $requestBody = (string) $request->getBody();
                dump([
                    'method' => $request->getMethod(),
                    'uri' => (string) $request->getUri(),
                    'headers' => array_merge($request->getHeaders(), $options[RequestOptions::HEADERS] ?? []),
                    'raw_body' => $requestBody,
                    'parsed_body' => Str::isJson($requestBody) ? Json::decode($requestBody) : [],
                ]);
            };

            $middlewareImpl = static function (RequestInterface $request, array $options) use ($onRequestNonNull, $die): \Psr\Http\Message\RequestInterface {
                $onRequestNonNull($request, $options);

                if ($die) {
                    exit(1);
                }

                return $request;
            };

            $handlerStack->push(function (callable $handler) use ($middlewareImpl): \Closure {
                return function (RequestInterface $request, array $options) use ($handler, $middlewareImpl): PromiseInterface {
                    return $handler($middlewareImpl($request, $options), $options);
                };
            }, 'debugRequestMiddleware');

            return tap($this)->setHandler($handlerStack);
        });

        PendingRequest::macro('debugResponse', function (?callable $onResponse = null, bool $die = false): PendingRequest {
            /** @var PendingRequest $this */

            /** @var HandlerStack $handlerStack */
            $handlerStack = resolve(HandlerStack::class);
            $handlerStack->remove('debugResponseMiddleware');

            $onResponseNonNull = $onResponse ?? static function (ResponseInterface $response): void {
                $body = (string) $response->getBody();
                dump([
                    'status' => $response->getStatusCode(),
                    'headers' => $response->getHeaders(),
                    'raw_body' => $body,
                    'parsed_body' => Str::isJson($body) ? \Illuminate\Database\Eloquent\Casts\Json::decode($body) : [],
                ]);
            };

            $middlewareImpl = static function (ResponseInterface $response) use ($onResponseNonNull, $die, $handlerStack): \Psr\Http\Message\ResponseInterface {
                $handlerStack->remove('debugResponseMiddleware');

                $onResponseNonNull($response);

                if ($die) {
                    exit(1);
                }

                return $response;
            };

            $handlerStack->push(Middleware::mapResponse($middlewareImpl), 'debugResponseMiddleware');

            return tap($this)->setHandler($handlerStack);
        });

        PendingRequest::macro('debug', function (bool $die = false): PendingRequest {
            /** @var PendingRequest $this */
            return $this->debugRequest()->debugResponse(die: $die);
        });

        PendingRequest::macro('baseUrlWithTemplate', function (string|Stringable|UriTemplate $template, iterable $variables = []): PendingRequest {
            /** @var PendingRequest $this */

            return tap($this, function (PendingRequest $request) use ($template, $variables): void {
                $request->baseUrl(
                    (string) \League\Uri\Uri::fromTemplate($template, $variables)
                );
            });
        });

        /**
         * Specify the number of times the request should be attempted.
         *
         * @param  array<int,int>|int  $times
         * @param  (Closure(int $attempts,\Illuminate\Http\Client\Request $request,?\Illuminate\Http\Client\Response $response):int)|int|null  $sleepMilliseconds
         * @param  (Closure(int $attempts,\Illuminate\Http\Client\Request $request,?\Illuminate\Http\Client\Response $response,?Exception $exception):bool)|null  $when
         */
        $withRetryMiddleware = function (array|int $times, null|Closure|int $sleepMilliseconds = null, ?Closure $when = null, bool $throw = true, ?string $name = null, ?bool $unique = false, ?bool $scoped = false, ?string $before = null, ?string $after = null): PendingRequest {
            /**
             * @var PendingRequest $this
             *
             * @phpstan-ignore varTag.nativeType
             */
            $backoff = [];
            if (is_array($times)) {
                $backoff = $times;
                $times = count($times) + 1;
            }
            $times--;
            $decider = function (
                int $attempts,
                RequestInterface $request,
                ?ResponseInterface $response = null,
                ?Exception $exception = null
            ) use ($times, $when): bool {
                if ($attempts > $times) {
                    return false;
                }
                $illuminateResponse = $response instanceof \Psr\Http\Message\ResponseInterface ? new \Illuminate\Http\Client\Response($response) : null;
                if ($illuminateResponse && $illuminateResponse->failed()) {
                    $exception = $illuminateResponse->toException() ?? $exception;
                }
                if ($when instanceof \Closure) {
                    return $when($attempts, new \Illuminate\Http\Client\Request($request), $illuminateResponse, $exception);
                }

                return false;
            };
            $delay = function (
                int $attempts,
                ?ResponseInterface $response,
                RequestInterface $request
            ) use ($sleepMilliseconds, $backoff): int {
                $delay = $backoff[$attempts - 1] ?? $sleepMilliseconds ?? \GuzzleHttp\RetryMiddleware::exponentialDelay($attempts);
                $illuminateResponse = $response instanceof \Psr\Http\Message\ResponseInterface ? new \Illuminate\Http\Client\Response($response) : null;

                // If closure provided for dynamic delay
                return value($delay, $attempts, new \Illuminate\Http\Client\Request($request), $illuminateResponse);
            };
            /** @var (callable(callable):(callable(RequestInterface,array):PromiseInterface)) */
            $middleware = Middleware::retry($decider, $delay);
            $middlewareImpl = (function (callable $handler) use ($middleware, $throw): \Closure {
                return function (RequestInterface $request, array $options) use ($handler, $throw, $middleware) {
                    /** @var PromiseInterface */
                    $promise = $middleware($handler)($request, $options);
                    if (! $throw) {
                        return $promise;
                    }

                    return $promise->then(
                        function ($response) {
                            return $response;
                        },
                        function ($reason) {
                            // Only throw after retries exhausted
                            throw_if($reason instanceof Exception, $reason);

                            return $reason;
                        }
                    );
                };
            });
            if ($name) {
                return $this->withNamedMiddleware($name, $middlewareImpl, $unique, $scoped, $before, $after);
            }
            if ($before) {
                return $this->withMiddlewareBefore($before, $middlewareImpl);
            }
            if ($after) {
                return $this->withMiddlewareAfter($after, $middlewareImpl);
            }

            return $this->withMiddleware($middlewareImpl);
        };
        PendingRequest::macro('withRetryMiddleware', $withRetryMiddleware);

        PendingRequest::macro('withRequestNormalizationMiddleware', function (): PendingRequest {
            /**
             * @disregard P1056
             *
             * @var PendingRequest $this
             */
            /** @var \Illuminate\Support\Collection<int,callable> $middlewares */
            // @phpstan-ignore-next-line property.protected
            $middlewares = $this->middleware;
            // Find if this middleware already exists
            $existingIndex = $middlewares->search(
                function (callable $middlewareFn): bool {
                    return $middlewareFn instanceof HttpClientNamedMiddleware
                        && 'request_normalizer' === $middlewareFn->name;
                }
            );
            if (false === $existingIndex) {
                /**
                 * @disregard P1056
                 *
                 * @phpstan-ignore-next-line property.protected
                 */
                $this->middleware = $middlewares->push(new HttpClientNamedMiddleware('request_normalizer', $this, function (callable $handler): \Closure {
                    return function (RequestInterface $request, array $options) use ($handler) {
                        /**
                         * @disregard P1056
                         *
                         * @var PendingRequest $this
                         *
                         * @phpstan-ignore-next-line property.protected
                         */
                        $options = $this->normalizeRequestOptions($this->mergeOptions($options));

                        return $handler($request, $options);
                    };
                }));
            }

            return $this;
        });

        PendingRequest::macro('withoutRequestNormalizationMiddleware', function (): PendingRequest {
            /**
             * @disregard P1056
             *
             * @var PendingRequest $this
             */
            return $this->withMiddleware(function (callable $handler): \Closure {
                /**
                 * @var PendingRequest $this
                 */
                /** @var \Illuminate\Support\Collection<int,callable> $middlewares */
                // @phpstan-ignore-next-line property.protected
                $middlewares = $this->middleware;
                // Find if this middleware already exists
                $existingIndex = $middlewares->search(
                    function (callable $middlewareFn): bool {
                        return $middlewareFn instanceof HttpClientNamedMiddleware
                            && 'request_normalizer' === $middlewareFn->name;
                    }
                );
                $middleware = null;
                if (false !== $existingIndex) {
                    /** @var HttpClientNamedMiddleware */
                    $middleware = $middlewares[$existingIndex];
                    $middleware->disable();
                }

                return function (RequestInterface $request, array $options) use ($handler, $middleware): PromiseInterface {
                    /** @var PromiseInterface */
                    $result = $handler($request, $options);

                    return $result->then(function ($response) use ($middleware): ResponseInterface {
                        if ($middleware) {
                            $middleware->enable();
                        }

                        return $response;
                    });
                };
            });
        });

        PendingRequest::macro('getRequest', function (): ?\Illuminate\Http\Client\Request {
            /** @var PendingRequest $this */
            return data_get($this, 'request');
        });

        /**
         * @param  callable(\App\Support\Http\LazyHttpClientPool):(Generator<array-key,(callable():\GuzzleHttp\Promise\PromiseInterface)|\GuzzleHttp\Promise\PromiseInterface>|iterable<(callable():\GuzzleHttp\Promise\PromiseInterface)|\GuzzleHttp\Promise\PromiseInterface>|list<(callable():\GuzzleHttp\Promise\PromiseInterface)|\GuzzleHttp\Promise\PromiseInterface>|void)  $callback
         * @return array<array-key,\Illuminate\Http\Client\Response>
         */
        $ofLimit = function (callable $callback, int $concurrency = 25): array {
            $results = [];
            $asyncPool = resolve(LazyHttpClientPool::class);
            /** @var (Generator<array-key,(callable():\GuzzleHttp\Promise\PromiseInterface)|\GuzzleHttp\Promise\PromiseInterface>|iterable<(callable():\GuzzleHttp\Promise\PromiseInterface)|\GuzzleHttp\Promise\PromiseInterface>|list<(callable():\GuzzleHttp\Promise\PromiseInterface)|\GuzzleHttp\Promise\PromiseInterface>|null) */
            $returned = $callback($asyncPool);
            /** @var Generator<array-key,\App\Support\Http\LazyHttpClient|(callable():\GuzzleHttp\Promise\PromiseInterface)|\GuzzleHttp\Promise\PromiseInterface|iterable> $iterable */
            $iterable = Promise\Create::iterFor($returned ?? $asyncPool->lazyClients);
            $requests = static function () use ($iterable) {
                foreach ($iterable as $key => $rfn) {
                    if (is_callable($rfn)) {
                        $rfn = $rfn();
                    }
                    if ($rfn instanceof LazyHttpClient && ! $rfn->key) {
                        $rfn->as($key);
                    }
                    if (is_iterable($rfn)) {
                        foreach ($rfn as $key => $value) {
                            if ($key) {
                                yield $key => $value;
                            } else {
                                yield $value;
                            }
                        }
                    } else {
                        yield $key => $rfn;
                    }
                }
            };
            $each = Promise\Each::ofLimit(
                $requests(),
                $concurrency,
                static function ($response, $index) use (&$results): void {
                    $results[$index] = $response;
                },
                static function ($reason, $index) use (&$results): void {
                    $results[$index] = $reason;
                }
            );
            $each->wait();

            return $results;
        };
        Http::macro('ofLimit', $ofLimit);
        /**
         * @param  Closure((callable(RequestInterface, array):PromiseInterface)):(Closure(RequestInterface, array):PromiseInterface)  $fn
         */
        $withScopedMiddleware = function (Closure $fn): PendingRequest {
            /**
             * @var PendingRequest $this
             *
             * @phpstan-ignore varTag.nativeType
             */
            $fn = $fn->bindTo($this, static::class);

            return $this->withMiddlewareBefore('request_normalizer', $fn);
        };
        PendingRequest::macro('withScopedMiddleware', $withScopedMiddleware);
        /**
         * @param  callable((callable(RequestInterface, array):PromiseInterface)):(callable(RequestInterface, array):PromiseInterface)  $fn
         */
        $withMiddlewareBefore = function (string $before, callable $fn): PendingRequest {
            /** @var PendingRequest $this */
            /**
             * @var Collection<int,callable>
             *
             * @phpstan-ignore-next-line property.protected
             */
            $middlewares = $this->middleware;
            // Determine position for insertion
            $targetName = $before;
            $targetIndex = $middlewares->search(
                fn (callable $middlewareFn): bool => $middlewareFn instanceof HttpClientNamedMiddleware
                    && $middlewareFn->name === $targetName
            );
            if (false === $targetIndex) {
                return $this;
            }
            $insertIndex = $targetIndex;
            // Insert before  target
            $middlewares->splice($insertIndex, 0, [$fn]);
            /**
             * @disregard P1056
             *
             * @phpstan-ignore property.protected
             * */
            $this->middleware = $middlewares;

            return $this;
        };
        PendingRequest::macro('withMiddlewareBefore', $withMiddlewareBefore);
        /**
         * @param  callable((callable(RequestInterface, array):PromiseInterface)):(callable(RequestInterface, array):PromiseInterface)  $fn
         */
        $withMiddlewareAfter = function (string $after, callable $fn): PendingRequest {
            /** @var PendingRequest $this */
            /**
             * @var Collection<int,callable>
             *
             * @phpstan-ignore-next-line property.protected
             */
            $middlewares = $this->middleware;
            // Determine position for insertion
            $targetName = $after;
            $targetIndex = $middlewares->search(
                fn (callable $middlewareFn): bool => $middlewareFn instanceof HttpClientNamedMiddleware
                    && $middlewareFn->name === $targetName
            );
            if (false === $targetIndex) {
                return $this;
            }
            $insertIndex = $targetIndex + 1;
            // Insert before  target
            $middlewares->splice($insertIndex, 0, [$fn]);
            /**
             * @disregard P1056
             *
             * @phpstan-ignore property.protected
             * */
            $this->middleware = $middlewares;

            return $this;
        };
        PendingRequest::macro('withMiddlewareAfter', $withMiddlewareAfter);
        /**
         * Register a named middleware, optionally positioning it before or after another one.
         *
         * @param  string  $name  Middleware name to add or replace
         * @param  Closure((callable(RequestInterface, array):PromiseInterface),PendingRequest):(Closure(RequestInterface, array):PromiseInterface)  $fn
         * @param  bool  $unique  If true, skip adding if it already exists
         * @param  string|null  $before  Insert before this named middleware
         * @param  string|null  $after  Insert after this named middleware
         */
        $withNamedMiddleware = function (
            string $name,
            Closure $fn,
            bool $unique = false,
            bool $scoped = false,
            ?string $before = null,
            ?string $after = null
        ): PendingRequest {
            /** @var PendingRequest $this */
            /** @var \Illuminate\Support\Collection<int, callable> $middlewares */
            // @phpstan-ignore-next-line property.protected
            $middlewares = $this->middleware;
            // 1️⃣ Find existing middleware with the same name
            $existingIndex = $middlewares->search(
                fn (callable $middlewareFn): bool => $middlewareFn instanceof HttpClientNamedMiddleware
                    && $middlewareFn->name === $name
            );
            if ($unique && false !== $existingIndex) {
                return $this;
            }
            // 2️⃣ Scoped binding if requested
            if ($scoped) {
                $fn = $fn->bindTo($this, static::class);
            }
            // 3️⃣ Create the named middleware
            $new = new HttpClientNamedMiddleware($name, $this, $fn);
            // 4️⃣ Replace existing one if found
            if (false !== $existingIndex) {
                // @phpstan-ignore property.protected
                $this->middleware[$existingIndex] = $new;

                return $this;
            }
            // Helper to get index by name
            $findIndex = function (string $middlewareName) use ($middlewares): ?int {
                $index = $middlewares->search(
                    function (callable $middlewareFn) use ($middlewareName): bool {
                        return $middlewareFn instanceof HttpClientNamedMiddleware
                            && $middlewareFn->name === $middlewareName;
                    }
                );

                return false === $index ? null : $index;
            };
            $normalizerIndex = $findIndex('request_normalizer');
            $afterIndex = $after ? $findIndex($after) : null;
            $beforeIndex = $before ? $findIndex($before) : null;
            // 5️⃣ Handle `before` rule normally
            if (null !== $before) {
                // If the referenced middleware is after or equal to request_normalizer, handle it accordingly
                if (null !== $beforeIndex && null !== $normalizerIndex && $beforeIndex >= $normalizerIndex) {
                    return $this->withMiddlewareBefore('request_normalizer', $new);
                }

                return $this->withMiddlewareBefore($before, $new);
            }
            // 6️⃣ Handle `after` but enforce ordering constraint
            if (null !== $after) {
                // If the referenced middleware is after or equal to request_normalizer, handle it accordingly
                if (null !== $afterIndex && null !== $normalizerIndex && $afterIndex >= $normalizerIndex) {
                    return $this->withMiddlewareBefore('request_normalizer', $new);
                }

                return $this->withMiddlewareAfter($after, $new);
            }

            // 7️⃣ Default — always before request_normalizer
            return $this->withMiddlewareBefore('request_normalizer', $new);
        };
        PendingRequest::macro('withNamedMiddleware', $withNamedMiddleware);
        /**
         * @param  Closure(RequestInterface,array):PromiseInterface  $fn
         */
        $withNamedRequestMiddleware = function (string $name, Closure $fn, bool $unique = false): PendingRequest {
            /**
             * @var PendingRequest $this
             */
            // @phpstan-ignore varTag.nativeType
            return $this->withNamedMiddleware($name, Middleware::mapRequest($fn), $unique);
        };
        PendingRequest::macro('withNamedRequestMiddleware', $withNamedRequestMiddleware);
        /**
         * @param  Closure(ResponseInterface):PromiseInterface  $fn
         */
        $withNamedResponseMiddleware = function (string $name, Closure $fn, bool $unique = false): PendingRequest {
            /**
             * @var PendingRequest $this
             */
            // @phpstan-ignore varTag.nativeType
            return $this->withNamedMiddleware($name, Middleware::mapResponse($fn), $unique);
        };
        PendingRequest::macro('withNamedResponseMiddleware', $withNamedResponseMiddleware);
        /**
         * Register a named before-sending callback, optionally positioning it before or after another one.
         *
         * @param  string  $name  Callback name to add or replace
         * @param  Closure(\Illuminate\Http\Client\Request,array,PendingRequest):(\Illuminate\Http\Client\Request|RequestInterface)  $fn
         * @param  bool  $unique  If true, skip adding if it already exists
         * @param  string|null  $before  Insert before this named callback
         * @param  string|null  $after  Insert after this named callback
         */
        $beforeSendingWithName = function (
            string $name,
            Closure $fn,
            bool $unique = false,
            ?string $before = null,
            ?string $after = null
        ): PendingRequest {
            /** @var PendingRequest $this */
            /** @var \Illuminate\Support\Collection<int,callable> $callbacks */
            // @phpstan-ignore-next-line property.protected
            $callbacks = $this->beforeSendingCallbacks;
            // Find if this callback already exists
            $existingIndex = $callbacks->search(
                function (callable $callback) use ($name): bool {
                    return $callback instanceof HttpClientNamedBeforeSending
                        && $callback->name === $name;
                }
            );
            if ($unique && false !== $existingIndex) {
                return $this;
            }
            $new = new HttpClientNamedBeforeSending($name, $fn);
            // Replace existing one if found
            if (false !== $existingIndex) {
                // @phpstan-ignore property.protected
                $this->beforeSendingCallbacks[$existingIndex] = $new;

                return $this;
            }
            // Determine position for insertion
            if (null !== $before || null !== $after) {
                $targetName = $before ?? $after;
                $targetIndex = $callbacks->search(
                    function (callable $callback) use ($targetName): bool {
                        return $callback instanceof HttpClientNamedBeforeSending
                            && $callback->name === $targetName;
                    }
                );
                if (false === $targetIndex) {
                    return $this->beforeSending($new);
                }
                $insertIndex = null !== $before
                    ? $targetIndex
                    : $targetIndex + 1;
                // Insert before or after target
                $callbacks = $callbacks->splice($insertIndex, 0, [$new]);
                /**
                 * @disregard P1056
                 *
                 * @phpstan-ignore property.protected
                 */
                $this->beforeSendingCallbacks = $callbacks;

                return $this;
            }

            // Default: append to end
            return $this->beforeSending($new);
        };
        PendingRequest::macro('beforeSendingWithName', $beforeSendingWithName);
        PendingRequest::macro('withoutBeforeSending', function (string $name): PendingRequest {
            /** @var PendingRequest $this */
            // @phpstan-ignore-next-line property.protected
            $filtered = $this->beforeSendingCallbacks
                ->reject(function (callable $middlewareFn) use ($name): bool {
                    return $middlewareFn instanceof HttpClientNamedBeforeSending && $middlewareFn->name === $name;
                });
            /**
             * @disregard P1056
             *
             * @phpstan-ignore property.protected
             */
            $this->beforeSendingCallbacks = $filtered;

            return $this;
        });
        PendingRequest::macro('withoutMiddleware', function (string $name): PendingRequest {
            /** @var PendingRequest $this */
            // @phpstan-ignore-next-line property.protected
            $filtered = $this->middleware
                ->reject(function (callable $middlewareFn) use ($name): bool {
                    return $middlewareFn instanceof HttpClientNamedMiddleware && $middlewareFn->name === $name;
                });
            /**
             * @disregard P1056
             *
             * @phpstan-ignore property.protected
             */
            $this->middleware = $filtered;

            return $this;
        });
    }
}
