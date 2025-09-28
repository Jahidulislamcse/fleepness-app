<?php

namespace App\Providers;

use Closure;
use Exception;
use Stringable;
use GuzzleHttp\Utils;
use Psr\Log\LogLevel;
use GuzzleHttp\Middleware;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use League\Uri\UriTemplate;
use App\Services\SMSService;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Request;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\RetryMiddleware;
use GuzzleHttp\MessageFormatter;
use League\Uri\Uri as LeagueUri;
use App\Support\Sms\SmsApiConnector;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Application;
use Illuminate\Log\Context\Repository;
use Psr\Http\Message\RequestInterface;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Spatie\Activitylog\Facades\LogBatch;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Client\PendingRequest;
use App\Support\Broadcaster\FcmBroadcaster;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Notification;
use App\Support\Notification\Channels\FcmTopicChannel;
use App\Support\Notification\Channels\FcmDeviceChannel;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Http\Client\Response as HttpClientResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(HandlerStack::class, function (): HandlerStack {
            $stack = new HandlerStack;

            return tap($stack)->setHandler(Utils::chooseHandler());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Notification::extend('fcm-device', function (Application $app) {
            return $app->make(FcmDeviceChannel::class);
        });

        Notification::extend('fcm-topic', function (Application $app) {
            return $app->make(FcmTopicChannel::class);
        });

        context()->hydrated(static function (Repository $context): void {
            if ($context->has('traceId') && $traceId = $context->get('traceId')) {
                LogBatch::setBatch($traceId);
            }
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Broadcast::extend('fcm', function (Application $app, array $config) {
            return $app->make(FcmBroadcaster::class);
        });

        Uri::macro('fromTemplate', fn (string|Stringable|UriTemplate $template, iterable $variables = []): Uri => Uri::of(LeagueUri::fromTemplate($template, $variables)));

        Http::globalOptions([
            RequestOptions::DEBUG => $this->app->isLocal()
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

        Http::globalRequestMiddleware(static fn (RequestInterface $request) => LogBatch::withinBatch(static fn ($reqTraceId) => $request->withHeader(
            'x-trace-id', $reqTraceId
        )));

        Http::globalResponseMiddleware(static fn (ResponseInterface $response) => LogBatch::withinBatch(static fn ($reqTraceId) => $response->withHeader(
            'x-trace-id', $reqTraceId
        )));

        $this->app->singleton(SMSService::class, function (Application $app) {
            return new SMSService(Http::sms());
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

            $onRequestNonNull = $onRequest ?? static function (RequestInterface $request): void {
                $body = (string) $request->getBody();

                dump([
                    'method' => $request->getMethod(),
                    'uri' => (string) $request->getUri(),
                    'headers' => $request->getHeaders(),
                    'raw_body' => $body,
                    'parsed_body' => Str::isJson($body) ? json_decode($body, true) : [],
                ]);
            };

            $middlewareImpl = static function (RequestInterface $request) use ($onRequestNonNull, $die, $handlerStack): \Psr\Http\Message\RequestInterface {
                $handlerStack->remove('debugRequestMiddleware');

                $onRequestNonNull($request);

                if ($die) {
                    exit(1);
                }

                return $request;
            };

            $handlerStack->push(Middleware::mapRequest($middlewareImpl), 'debugRequestMiddleware');

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
                    'parsed_body' => Str::isJson($body) ? json_decode($body, true) : [],
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
                    Uri::fromTemplate($template, $variables)->value()
                );
            });
        });

        PendingRequest::macro(
            'withRetryMiddleware',
            /**
             * Specify the number of times the request should be attempted.
             *
             * @param  array<int,int>|int  $times
             * @param  (Closure(int $attempts,\Illuminate\Http\Client\Request $request,?\Illuminate\Http\Client\Response $response):int)|int|null  $sleepMilliseconds
             * @param  (Closure(int $attempts,\Illuminate\Http\Client\Request $request,?\Illuminate\Http\Client\Response $response,?Exception $exception):bool)|null  $when
             */
            function (array|int $times, null|Closure|int $sleepMilliseconds = null, ?Closure $when = null, bool $throw = true): PendingRequest {
                /** @var PendingRequest $this */
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

                    $illuminateResponse = $response ? new HttpClientResponse($response) : null;

                    if ($illuminateResponse && $illuminateResponse->failed()) {
                        $exception = $illuminateResponse->toException() ?? $exception;
                    }

                    if ($when) {
                        return $when($attempts, new HttpClientRequest($request), $illuminateResponse, $exception);
                    }

                    return false;
                };

                $delay = function (
                    int $attempts,
                    ?ResponseInterface $response,
                    RequestInterface $request
                ) use ($sleepMilliseconds, $backoff): int {
                    $delay = $backoff[$attempts - 1] ?? $sleepMilliseconds ?? RetryMiddleware::exponentialDelay($attempts);
                    $illuminateResponse = $response ? new HttpClientResponse($response) : null;

                    // If closure provided for dynamic delay
                    return value($delay, $attempts, new HttpClientRequest($request), $illuminateResponse);
                };

                $middleware = Middleware::retry($decider, $delay);

                return $this->withMiddleware(function (callable $handler) use ($middleware, $throw) {
                    return function (RequestInterface $request, array $options) use ($handler, $throw, $middleware) {
                        /** @var \GuzzleHttp\Promise\PromiseInterface */
                        $promise = $middleware($handler)($request, $options);

                        if (! $throw) {
                            return $promise;
                        }

                        return $promise->then(
                            fn ($response) => $response,
                            function ($reason) {
                                // Only throw after retries exhausted
                                if ($reason instanceof Exception) {
                                    throw $reason;
                                }

                                return $reason;
                            }
                        );
                    };
                });

            }
        );
    }
}
