<?php

namespace App\Services;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\EgressServiceClient;
use Agence104\LiveKit\EncodedOutputs;
use Agence104\LiveKit\RoomCreateOptions;
use Agence104\LiveKit\RoomServiceClient;
use Agence104\LiveKit\VideoGrant;
use App\Data\Dto\GeneratePublisherTokenData;
use App\Data\Dto\GenerateSubscriberTokenData;
use App\Models\Livestream;
use Carbon\Carbon;
use Closure;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Pipeline;
use Livekit\EncodedFileOutput;
use Livekit\EncodedFileType;
use Livekit\ImageOutput;
use Livekit\SegmentedFileOutput;

class LivestreamService
{
    public function __construct(
        protected readonly RoomServiceClient $roomService,
        protected readonly EgressServiceClient $egressService,
        #[Storage('r2')] protected readonly Filesystem&Cloud $r2fileSytem,
    ) {
    }

    public function generatePublisherToken(GeneratePublisherTokenData $data): string
    {
        return Pipeline::send($data)
            ->through([

                function (GeneratePublisherTokenData $data, Closure $next) {
                    $roomCreateOpts = tap(
                        resolve(RoomCreateOptions::class),
                        fn (RoomCreateOptions $opts) => $opts
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
                        fn (AccessTokenOptions $opts) => $opts
                            ->setIdentity($data->identity)
                            ->setName($data->displayName)
                    );

                    $videoGrant = tap(
                        resolve(VideoGrant::class),
                        fn (VideoGrant $grant) => $grant
                            ->setRoomName($roomName)
                            ->setRoomJoin()
                            ->setRoomAdmin()
                            ->setCanPublish()
                            ->setCanPublishData()
                    );

                    $roomTokenJwt = tap(
                        resolve(AccessToken::class),
                        fn (AccessToken $token) => $token
                            ->init($roomTokenOpts)
                            ->setGrant($videoGrant)
                    )
                        ->toJwt();

                    // dd($roomTokenOpts);

                    // $cacheTtl = Carbon::createFromTimestamp($roomTokenOpts->getTtl());

                    return $roomTokenJwt;
                },
            ])
            ->thenReturn();
    }

    public function generateSubscriberToken(GenerateSubscriberTokenData $data): string
    {
        return Pipeline::send($data->roomName)
            ->through([

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
                    // $cacheTtl = Carbon::createFromTimestamp($roomTokenOpts->getTtl());

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
        $segmentedFileOutput = resolve(SegmentedFileOutput::class);
        // dd($imageOutput);

        $output = resolve(EncodedOutputs::class)
            ->setFile($fileOutput)
            ->setSegments($segmentedFileOutput)
            ->setImage($imageOutput);

        return $this->egressService->startRoomCompositeEgress($roomName, 'single-speaker', $output);
    }

    public function stopRecording(string $egressId)
    {
        return $this->egressService->stopEgress($egressId);
    }

    public function getRecordingsFor(Livestream $livestream)
    {
        $listEgress = $this->egressService->listEgress($livestream->getRoomName(), $livestream->egress_id);

        /** @var Collection<int,EgressInfo> */
        $egressInfoCollection = collect($listEgress->getItems());

        return $egressInfoCollection
            ->flatMap(function ($egressInfo) {
                /** @var Collection<int,FileInfo> */
                $fileInfoCollection = collect($egressInfo->getFileResults());

                return $fileInfoCollection
                    ->map(function ($fileInfo) {
                        $filename = $fileInfo->getFilename();
                        $startedAt = $fileInfo->getStartedAt();
                        $endedAt = $fileInfo->getEndedAt();
                        $duration = $fileInfo->getDuration();
                        $size = $fileInfo->getSize();
                        $location = $fileInfo->getLocation();
                        $location = $this->r2fileSytem->url($filename);

                        return compact(
                            'filename',
                            'startedAt',
                            'endedAt',
                            'duration',
                            'size',
                            'location',
                        );
                    })
                    ->all();
            })
            ->all();
    }

    public function getThumbnailsFor(Livestream $livestream)
    {
        $listEgress = $this->egressService->listEgress($livestream->getRoomName(), $livestream->egress_id);

        /** @var Collection<int,EgressInfo> */
        $egressInfoCollection = collect($listEgress->getItems());

        return $egressInfoCollection
            ->flatMap(function ($egressInfo) {
                /** @var Collection<int,ImagesInfo> */
                $infoCollection = collect($egressInfo->getImageResults());

                return $infoCollection
                    ->map(function ($info) {
                        $filenamePrefix = $info->getFilenamePrefix();
                        $imageCount = $info->getImageCount();
                        $startedAt = $info->getStartedAt();
                        $endedAt = $info->getEndedAt();
                        $directoryName = str($filenamePrefix)->dirname();

                        $thumbnails = $this->r2fileSytem->files($directoryName);

                        $thumbnails = collect($thumbnails)
                            ->map(fn ($thmnailPath) => $this->r2fileSytem->url($thmnailPath))
                            ->all();

                        return compact(
                            'filenamePrefix',
                            'imageCount',
                            'startedAt',
                            'endedAt',
                            'thumbnails'
                        );
                    })
                    ->all();
            })
            ->all();
    }

    public function getShortVideosFor(Livestream $livestream)
    {
        $listEgress = $this->egressService->listEgress($livestream->getRoomName(), $livestream->egress_id);

        /** @var Collection<int,EgressInfo> */
        $egressInfoCollection = collect($listEgress->getItems());

        return $egressInfoCollection
            ->flatMap(function ($egressInfo) {
                /** @var Collection<int,SegmentsInfo> */
                $infoCollection = collect($egressInfo->getSegmentResults());

                return $infoCollection
                    ->map(function ($info) {
                        $playlistName = $info->getPlaylistName();
                        $livePlaylistName = $info->getLivePlaylistName();
                        $duration = $info->getDuration();
                        $size = $info->getSize();
                        $playlistLocation = $info->getPlaylistLocation();
                        $livePlaylistLocation = $info->getLivePlaylistLocation();
                        $segmentCount = $info->getSegmentCount();
                        $startedAt = $info->getStartedAt();
                        $endedAt = $info->getEndedAt();

                        $playlistLocation = $this->r2fileSytem->url($playlistName);

                        return compact(
                            'playlistName',
                            'livePlaylistName',
                            'duration',
                            'size',
                            'playlistLocation',
                            'livePlaylistLocation',
                            'segmentCount',
                            'startedAt',
                            'endedAt',
                        );
                    })
                    ->all();
            })
            ->all();
    }
}
