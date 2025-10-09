<?php

namespace App\Support\Livekit\Contracts;

use Livekit\EgressInfo;
use Livekit\ImageOutput;
use Livekit\StreamOutput;
use Livekit\EncodingOptions;
use Livekit\EncodedFileOutput;
use Livekit\ListEgressResponse;
use Livekit\SegmentedFileOutput;
use Livekit\EncodingOptionsPreset;
use Agence104\LiveKit\EncodedOutputs;

interface EgressServiceClient
{
    public function getOutputParams(
        EncodedFileOutput|EncodedOutputs|ImageOutput|SegmentedFileOutput|StreamOutput $output,
        null|EncodingOptions|EncodingOptionsPreset $options = null
    ): array;

    public function startRoomCompositeEgress(
        string $roomName,
        string $layout,
        EncodedFileOutput|EncodedOutputs|ImageOutput|SegmentedFileOutput|StreamOutput $output,
        null|EncodingOptions|EncodingOptionsPreset $options = null,
        bool $audioOnly = false,
        bool $videoOnly = false,
        string $customBaseUrl = ''
    ): EgressInfo;

    public function startWebEgress(
        string $url,
        EncodedFileOutput|EncodedOutputs|ImageOutput|SegmentedFileOutput|StreamOutput $output,
        null|EncodingOptions|EncodingOptionsPreset $options = null,
        bool $audioOnly = false,
        bool $videoOnly = false,
        bool $awaitStartSignal = false
    ): EgressInfo;

    public function startTrackCompositeEgress(
        string $roomName,
        EncodedFileOutput|EncodedOutputs|ImageOutput|SegmentedFileOutput|StreamOutput $output,
        string $audioTrackId = '',
        string $videoTrackId = '',
        null|EncodingOptions|EncodingOptionsPreset $options = null,
    ): EgressInfo;

    public function startTrackEgress(
        string $roomName,
        \Livekit\DirectFileOutput|string $output,
        string $trackId
    ): EgressInfo;

    public function updateLayout(
        string $egressId,
        string $layout
    ): EgressInfo;

    public function updateStream(
        string $egressId,
        array $addOutputUrls = [],
        array $removeOutputUrls = []
    ): EgressInfo;

    public function listEgress(
        string $roomName = '',
        string $egressId = '',
        bool $active = false
    ): ListEgressResponse;

    public function stopEgress(string $egressId): EgressInfo;
}
