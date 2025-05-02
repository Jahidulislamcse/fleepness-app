<?php

namespace App\Services;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\RoomCreateOptions;
use Agence104\LiveKit\RoomServiceClient;
use Agence104\LiveKit\VideoGrant;
use Agence104\LiveKit\EgressServiceClient;
use Agence104\LiveKit\EncodedOutputs;
use App\Data\Dto\GeneratePublisherTokenData;
use App\Data\Dto\GenerateSubscriberTokenData;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Livekit\EncodedFileOutput;
use Livekit\EncodedFileType;
use Livekit\ImageOutput;
use Closure;
use Illuminate\Support\Facades\Pipeline;

class LivestreamService
{
    public function __construct(
        protected readonly RoomServiceClient $roomService,
        protected readonly EgressServiceClient $egressService,

    ) {}

    public function generatePublisherToken(GeneratePublisherTokenData $data): string
    {
        return Pipeline::send($data)
            ->through([
                function (GeneratePublisherTokenData $data, Closure $next): string {
                    /** @var string */
                    $roomToken = Cache::get($data->roomName);

                    if ($roomToken) {
                        return $roomToken;
                    }

                    return $next($data);
                },
                function (GeneratePublisherTokenData $data, Closure $next) {
                    $roomCreateOpts = tap(
                        resolve(RoomCreateOptions::class),
                        fn(RoomCreateOptions $opts) => $opts
                            ->setName($data->roomName)
                            ->setMetadata(json_encode($data->metadata))
                    );

                    $this->roomService->createRoom($roomCreateOpts);

                    return $next($data);
                },
                function (GeneratePublisherTokenData $data, Closure $next): string {
                    $roomName = $data->roomName;

                    $roomTokenOpts = tap(
                        resolve(AccessTokenOptions::class),
                        fn(AccessTokenOptions $opts) => $opts
                            ->setIdentity($data->identity)
                            ->setName($data->displayName)
                    );

                    $videoGrant = tap(
                        resolve(VideoGrant::class),
                        fn(VideoGrant $grant) => $grant
                            ->setRoomName($roomName)
                            ->setRoomJoin()
                            ->setRoomAdmin()
                            ->setCanPublish()
                            ->setCanPublishData()
                    );

                    $roomTokenJwt = tap(
                        resolve(AccessToken::class),
                        fn(AccessToken $token) => $token
                            ->init($roomTokenOpts)
                            ->setGrant($videoGrant)
                    )
                        ->toJwt();

                    $cacheTtl = Carbon::createFromTimestamp($roomTokenOpts->getTtl());

                    Cache::put($roomName, $roomTokenJwt, $cacheTtl);

                    return $roomTokenJwt;
                },
            ])
            ->thenReturn();
    }

    public function generateSubscriberToken(GenerateSubscriberTokenData $data): string
    {
        return Pipeline::send($data->roomName)
            ->through([
                function (string $roomName, Closure $next): string {
                    /** @var string */
                    $roomToken = Cache::get($roomName);

                    if ($roomToken) {
                        return $roomToken;
                    }

                    return $next($roomName);
                },
                function (string $roomName, Closure $next) use ($data): string {
                    $roomToken = resolve(AccessToken::class);
                    $roomTokenOpts = (new AccessTokenOptions())
                        ->setIdentity($data->identity)
                        ->setName($data->displayName)
                        ->setMetadata(json_encode($data->metadata));

                    $videoGrant = (new VideoGrant())
                        ->setRoomName($roomName)
                        ->setRoomJoin()
                        ->setCanPublish(false)
                        ->setCanPublishData(! $data->isPublic);

                    $roomTokenJwt = $roomToken->init($roomTokenOpts)->setGrant($videoGrant)->toJwt();
                    $cacheTtl = Carbon::createFromTimestamp($roomTokenOpts->getTtl());

                    Cache::put($roomName, $roomTokenJwt, $cacheTtl);

                    return $roomTokenJwt;
                },
            ])
            ->thenReturn();
    }
    public function startRecording(string $roomName, string $outputPath)
    {
           $fileOutput = resolve(EncodedFileOutput::class)
            ->setFileType(EncodedFileType::MP4)
            ->setFilepath($outputPath);
        $imageOutput = resolve(ImageOutput::class);

        $output = resolve(EncodedOutputs::class)
            ->setFile($fileOutput)
            ->setImage($imageOutput);

        return $this->egressService->startRoomCompositeEgress($roomName, 'single-speaker', $output);
    }

    public function stopRecording(string $egressId)
    {
        return $this->egressService->stopEgress($egressId);
    }
}
