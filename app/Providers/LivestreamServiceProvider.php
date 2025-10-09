<?php

namespace App\Providers;

use Livekit\Egress;
use Livekit\S3Upload;
use Livekit\ImageOutput;
use Livekit\ImageFileSuffix;
use Livekit\EncodedFileOutput;
use Livekit\SegmentedFileOutput;
use Livekit\SegmentedFileSuffix;
use Agence104\LiveKit\AccessToken;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use App\Support\Livekit\RoomServiceClient;
use App\Support\Livekit\EgressServiceClient;
use App\Support\Livekit\RoomProtobufService;
use App\Support\Livekit\EgressProtobufService;
use Livekit\RoomService as RoomServiceContract;
use App\Support\Livekit\Contracts\RoomServiceClient as RoomServiceClientContract;
use App\Support\Livekit\Contracts\EgressServiceClient as EgressServiceClientContract;

class LivestreamServiceProvider extends ServiceProvider
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

        $this->app->singleton(RoomServiceClientContract::class, function (Application $app) {
            return new RoomServiceClient(
                $app->make(RoomServiceContract::class),
                config('services.livekit.host'),
                config('services.livekit.api_key'),
                config('services.livekit.api_secret'),
            );
        });

        $this->app->singleton(RoomServiceContract::class, function (Application $app) {
            return new RoomProtobufService(
                config('services.livekit.host'),
            );
        });

        $this->app->singleton(EgressServiceClientContract::class, function (Application $app) {
            return new EgressServiceClient(
                $app->make(Egress::class),
                config('services.livekit.host'),
                config('services.livekit.api_key'),
                config('services.livekit.api_secret'),
            );
        });

        $this->app->singleton(Egress::class, function (Application $app) {
            return new EgressProtobufService(
                config('services.livekit.host'),
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

            return tap(new EncodedFileOutput)
                ->setFilepath('{room_name}/{time}')
                ->setS3($s3Config);
        });

        $this->app->bind(ImageOutput::class, function (Application $app) {
            $s3Config = $app->get(S3Upload::class);

            return tap(new ImageOutput)
                ->setS3($s3Config)
                ->setWidth(1280)
                ->setHeight(720)
                ->setFilenamePrefix('{room_name}/{time}')
                ->setFilenameSuffix(ImageFileSuffix::IMAGE_SUFFIX_TIMESTAMP)
                ->setCaptureInterval(config()->integer('services.livekit.egress.thumbnail_capture_inteval'));
        });

        $this->app->bind(SegmentedFileOutput::class, function (Application $app) {
            $s3Config = $app->get(S3Upload::class);

            return tap(new SegmentedFileOutput)
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
