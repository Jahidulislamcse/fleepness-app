<?php

namespace App\Services;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\RoomCreateOptions;
use Agence104\LiveKit\RoomServiceClient;
use Agence104\LiveKit\VideoGrant;
use App\Data\Dto\GeneratePublisherTokenData;
use App\Data\Dto\GenerateSubscriberTokenData;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Pipeline;

class LivestreamService
{
    public function __construct(protected readonly RoomServiceClient $roomService) {}

    public function generatePublisherToken(GeneratePublisherTokenData $data): string
    {
        return Pipeline::send($data)
            ->through([
                function (GeneratePublisherTokenData $data, Closure $next): string {
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
                        ->setName($data->displayName);

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
}
