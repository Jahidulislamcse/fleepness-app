<?php

namespace App\Providers;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\RoomServiceClient;
use Illuminate\Foundation\Application;
use Agence104\LiveKit\EgressServiceClient;
use Illuminate\Support\ServiceProvider;
use Livekit\EncodedFileOutput;
use Livekit\ImageFileSuffix;
use Livekit\ImageOutput;
use Livekit\S3Upload;

class LivekitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AccessToken::class, function (Application $app) {
            return new AccessToken(
                config('services.livekit.api_key'),
                config('services.livekit.api_secret')
            );
        });

        $this->app->singleton(RoomServiceClient::class, function (Application $app) {
            return new RoomServiceClient(
                config('services.livekit.host'),
                config('services.livekit.api_key'),
                config('services.livekit.api_secret'),
            );
        });
        $this->app->singleton(EgressServiceClient::class, function (Application $app) {
            return new EgressServiceClient(
                config('services.livekit.host'),
                config('services.livekit.api_key'),
                config('services.livekit.api_secret'),
            );
        });

        $this->app->bind(EncodedFileOutput::class, function (Application $app) {
            $s3Config = tap(new S3Upload)
                ->setAccessKey(config('filesystems.disks.s3.key'))
                ->setSecret(config('filesystems.disks.s3.key'))
                ->setBucket(config('filesystems.disks.s3.bucket'))
                ->setForcePathStyle(config('filesystems.disks.s3.use_path_style_endpoint'));

            return tap(new EncodedFileOutput())
                ->setS3($s3Config);
        });

        $this->app->bind(ImageOutput::class, function (Application $app) {
            $s3Config = tap(new S3Upload)
                ->setAccessKey(config('filesystems.disks.s3.key'))
                ->setSecret(config('filesystems.disks.s3.key'))
                ->setBucket(config('filesystems.disks.s3.bucket'))
                ->setForcePathStyle(config('filesystems.disks.s3.use_path_style_endpoint'));

            return tap(new ImageOutput())
                ->setS3($s3Config)
                ->setWidth(1280)
                ->setHeight(720)
                ->setFilenamePrefix('{room_name}/{publisher_identity}')
                ->setFilenameSuffix(ImageFileSuffix::IMAGE_SUFFIX_TIMESTAMP);
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
