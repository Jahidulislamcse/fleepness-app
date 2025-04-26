<?php

namespace App\Providers;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\RoomServiceClient;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class LivekitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AccessToken::class, function (Application $app) {
            return new AccessToken(
                config('services.livekit.api_key'),
                config('services.livekit.api_secret')
            );
        });

        $this->app->bind(RoomServiceClient::class, function (Application $app) {
            return new RoomServiceClient(
                config('services.livekit.host'),
                config('services.livekit.api_key'),
                config('services.livekit.api_secret'),
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
