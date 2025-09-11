<?php

namespace App\Providers;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\EgressServiceClient;
use Agence104\LiveKit\RoomServiceClient;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Livekit\EncodedFileOutput;
use Livekit\ImageFileSuffix;
use Livekit\ImageOutput;
use Livekit\S3Upload;
use Livekit\SegmentedFileOutput;
use Livekit\SegmentedFileSuffix;

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

        $this->app->singleton(S3Upload::class, function (Application $app) {
            return tap(new S3Upload)
                ->setAccessKey(config('services.livekit.egress.r2.key'))
                ->setSecret(config('services.livekit.egress.r2.secret'))
                ->setRegion(config('services.livekit.egress.r2.region'))
                ->setBucket(config('services.livekit.egress.r2.bucket'))
                ->setEndpoint(config('services.livekit.egress.r2.endpoint'))
                ->setForcePathStyle(config('services.livekit.egress.r2.use_path_style_endpoint'));
        });

        $this->app->bind(EncodedFileOutput::class, function (Application $app) {
            $s3Config = $app->get(S3Upload::class);

            return tap(new EncodedFileOutput())
                ->setFilepath('{room_name}/{time}')
                ->setS3($s3Config);
        });

        $this->app->bind(ImageOutput::class, function (Application $app) {
            $s3Config = $app->get(S3Upload::class);

            return tap(new ImageOutput())
                ->setS3($s3Config)
                ->setWidth(1280)
                ->setHeight(720)
                ->setFilenamePrefix('{room_name}/{time}')
                ->setFilenameSuffix(ImageFileSuffix::IMAGE_SUFFIX_TIMESTAMP)
                ->setCaptureInterval(config()->integer('services.livekit.egress.thumbnail_capture_inteval'));
        });

        $this->app->bind(SegmentedFileOutput::class, function (Application $app) {
            $s3Config = $app->get(S3Upload::class);

            return tap(new SegmentedFileOutput())
                ->setFilenamePrefix('{room_name}/{time}')
                ->setFilenameSuffix(SegmentedFileSuffix::TIMESTAMP)
                ->setS3($s3Config)->setSegmentDuration(config()->integer('services.livekit.egress.short_video_duration'));
        });
    }


    public function boot(): void
    {
        //
    }
}
