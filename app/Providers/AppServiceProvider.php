<?php

namespace App\Providers;

use Throwable;
use Stringable;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use League\Uri\UriTemplate;
use App\Services\SMSService;
use Illuminate\Http\Request;
use League\Uri\Uri as LeagueUri;
use Illuminate\Foundation\Application;
use Illuminate\Log\Context\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Spatie\Activitylog\Facades\LogBatch;
use Illuminate\Support\Facades\Broadcast;
use App\Support\Broadcaster\FcmBroadcaster;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Concurrency\ConcurrencyManager;
use App\Support\Notification\Channels\SmsChannel;
use App\Support\Notification\Channels\FcmTopicChannel;
use App\Support\Notification\Channels\FcmDeviceChannel;
use Illuminate\Support\Stringable as SupportStringable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Notification::resolved(function (ChannelManager $service): void {
            $service->extend('fcm-device', fn (Application $app) => $app->make(FcmDeviceChannel::class));

            $service->extend('fcm-topic', fn (Application $app) => $app->make(FcmTopicChannel::class));

            $service->extend('sms', fn (Application $app) => $app->make(SmsChannel::class));
        });

        Concurrency::resolved(function (ConcurrencyManager $service): void {
            $service->extend('octane', fn (Application $app, $config) => $app->make(\App\Support\Concurrency\Drivers\OctaneDriver::class, [
                'config' => $config,
            ]));
        });

        Broadcast::resolved(function (\Illuminate\Broadcasting\BroadcastManager $service): void {
            $service->extend('fcm', fn (Application $app, array $config) => $app->make(FcmBroadcaster::class));
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Json::decodeUsing(function (mixed $value, ?bool $associative = true) {
            if (extension_loaded('simdjson')) {
                try {
                    return simdjson_decode($value, $associative);
                } catch (Throwable) {
                }
            }

            return json_decode($value, $associative);
        });

        Str::macro('otp', function (int $length = 4): string {
            $otp = '';
            for ($i = 0; $i < $length; $i++) {
                if (! app()->isProduction()) {
                    $otp .= 1;
                } else {
                    $otp .= random_int(0, 9);
                }
            }

            return $otp;
        });

        Str::macro('orderId', function (string $prefix = '#ORD') {
            $prefix = str($prefix)
                ->trim()
                ->whenDoesntEndWith('-', fn (SupportStringable $str) => $str->append('-'))
                ->value();

            return str(Str::random(6))->prepend($prefix);
        });

        Model::unguard();
        Model::automaticallyEagerLoadRelationships();
        Model::shouldBeStrict(! app()->isProduction());

        context()->hydrated(static function (Repository $context): void {
            if ($context->has('traceId') && $traceId = $context->get('traceId')) {
                LogBatch::setBatch($traceId);
            }
        });

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        Uri::macro('fromTemplate', fn (string|Stringable|UriTemplate $template, iterable $variables = []): Uri => Uri::of(LeagueUri::fromTemplate($template, $variables)));

        $this->app->singleton(fn (Application $app): \App\Services\SMSService => new SMSService);

    }
}
