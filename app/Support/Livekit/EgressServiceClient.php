<?php

namespace App\Support\Livekit;

use Livekit\Egress;
use Livekit\EgressInfo;
use Livekit\ImageOutput;
use Livekit\StreamOutput;
use Livekit\EncodingOptions;
use Livekit\DirectFileOutput;
use Livekit\WebEgressRequest;
use Livekit\EncodedFileOutput;
use Livekit\ListEgressRequest;
use Livekit\StopEgressRequest;
use Livekit\ListEgressResponse;
use Livekit\TrackEgressRequest;
use Livekit\SegmentedFileOutput;
use Livekit\UpdateLayoutRequest;
use Livekit\UpdateStreamRequest;
use Agence104\LiveKit\VideoGrant;
use Livekit\EncodingOptionsPreset;
use Agence104\LiveKit\EncodedOutputs;
use Livekit\RoomCompositeEgressRequest;
use Agence104\LiveKit\BaseServiceClient;
use Livekit\TrackCompositeEgressRequest;
use Illuminate\Container\Attributes\Singleton;
use App\Support\Livekit\Contracts\EgressServiceClient as EgressServiceClientContract;

/**
 * Define the Egress service client.
 */
#[Singleton]
class EgressServiceClient extends BaseServiceClient implements EgressServiceClientContract
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        /**
         * The Twirp RPC adapter for client implementation.
         */
        protected readonly Egress $rpc,
        ?string $host = null,
        ?string $apiKey = null,
        ?string $apiSecret = null

    ) {
        parent::__construct($host, $apiKey, $apiSecret);
    }

    /**
     * Get the stream output parameters.
     *
     * @param  \Agence104\LiveKit\EncodedOutputs|\Livekit\EncodedFileOutput|\Livekit\StreamOutput|\Livekit\SegmentedFileOutput|\Livekit\ImageOutput  $output
     *                                                                                                                                                        The output stream.
     * @param  \Livekit\EncodingOptionsPreset|\Livekit\EncodingOptions|null  $options
     *                                                                                 The output options.
     * @return array
     *               The output parameters as an array.
     */
    public function getOutputParams(
        EncodedFileOutput|EncodedOutputs|ImageOutput|SegmentedFileOutput|StreamOutput $output,
        null|EncodingOptions|EncodingOptionsPreset $options = null
    ): array {
        $file = null;
        $fileOutputs = null;
        $stream = null;
        $streamOutputs = null;
        $preset = null;
        $advanced = null;
        $segments = null;
        $segmentOutputs = null;
        $imageOutputs = null;

        if ($output instanceof EncodedOutputs) {
            $fileOutputs = $output->getFile() ? [$output->getFile()] : null;
            $streamOutputs = $output->getStream() ? [$output->getStream()] : null;
            $segmentOutputs = $output->getSegments() ? [$output->getSegments()] : null;
            $imageOutputs = $output->getSegments() ? [$output->getImage()] : null;
        } elseif ($output instanceof EncodedFileOutput && ! empty($output->getFilepath())) {
            $file = $output;
            $fileOutputs = [$file];
        } elseif ($output instanceof SegmentedFileOutput && ! empty($output->getFilenamePrefix())) {
            $segments = $output;
            $segmentOutputs = [$segments];
            // @phpstan-ignore empty.expr
        } elseif ($output instanceof StreamOutput && ! empty($output->getUrls())) {
            $streamOutputs = [$output];
        } elseif ($output instanceof ImageOutput) {
            $imageOutputs = [$output];
        } else {
            $stream = $output;
        }

        if ($options) {
            if ($options instanceof EncodingOptionsPreset) {
                $preset = $options;
            } else {
                $advanced = $options;
            }
        }

        return [
            $file,
            $stream,
            $segments,
            $preset,
            $advanced,
            $fileOutputs,
            $streamOutputs,
            $segmentOutputs,
            $imageOutputs,
        ];
    }

    /**
     * Starts a room composite egress which uses a web template.
     *
     * @param  string  $roomName
     *                            The name of the room.
     * @param  string  $layout
     *                          The egress layout.
     * @param  \Agence104\LiveKit\EncodedOutputs|\Livekit\EncodedFileOutput|\Livekit\StreamOutput|\Livekit\SegmentedFileOutput|\Livekit\ImageOutput  $output
     *                                                                                                                                                        The egress output.
     * @param  \Livekit\EncodingOptionsPreset|\Livekit\EncodingOptions|null  $options
     *                                                                                 The encoding options or preset.
     * @param  bool  $audioOnly
     *                           The flag which defines if we record only the audio or not.
     * @param  bool  $videoOnly
     *                           The flag which defines if we record only the video or not.
     * @param  string  $customBaseUrl
     *                                 The custom template url. (default https://recorder.livekit.io)
     * @return \Livekit\EgressInfo
     *                             The egress info.
     */
    public function startRoomCompositeEgress(
        string $roomName,
        string $layout,
        EncodedFileOutput|EncodedOutputs|ImageOutput|SegmentedFileOutput|StreamOutput $output,
        null|EncodingOptions|EncodingOptionsPreset $options = null,
        bool $audioOnly = false,
        bool $videoOnly = false,
        string $customBaseUrl = ''
    ): EgressInfo {
        [
            $file,
            $stream,
            $segments,
            $preset,
            $advanced,
            $fileOutputs,
            $streamOutputs,
            $segmentOutputs,
            $imageOutputs,
        ] = $this->getOutputParams($output, $options);

        // Set the request data.
        $data = [
            'room_name' => $roomName,
            'layout' => $layout,
            'audio_only' => $audioOnly,
            'video_only' => $videoOnly,
            'custom_base_url' => $customBaseUrl,
        ];

        $data += array_filter([
            'file_outputs' => $fileOutputs,
            'stream_outputs' => $streamOutputs,
            'segment_outputs' => $segmentOutputs,
            'image_outputs' => $imageOutputs,
        ]);

        if ($file) {
            $data['file'] = $file;
        } elseif ($segments) {
            $data['segments'] = $segments;
        } elseif ($stream) {
            $data['stream'] = $stream;
        }

        if ($preset) {
            $data['preset'] = $preset;
        } else {
            $data['advanced'] = $advanced;
        }

        $videoGrant = new VideoGrant;
        $videoGrant->setRoomRecord();

        return $this->rpc->StartRoomCompositeEgress(
            $this->authHeader($videoGrant),
            new RoomCompositeEgressRequest($data)
        );
    }

    /**
     * Starts a Web egress.
     *
     * @param  string  $url
     *                       The URL of the web page to record.
     * @param  \Agence104\LiveKit\EncodedOutputs|\Livekit\EncodedFileOutput|\Livekit\StreamOutput|\Livekit\SegmentedFileOutput|\Livekit\ImageOutput  $output
     *                                                                                                                                                        The egress output.
     * @param  \Livekit\EncodingOptionsPreset|\Livekit\EncodingOptions|null  $options
     *                                                                                 The encoding options or preset.
     * @param  bool  $audioOnly
     *                           The flag which defines if we record only the audio or not.
     * @param  bool  $videoOnly
     *                           The flag which defines if we record only the video or not.
     * @param  bool  $awaitStartSignal
     *                                  The flag which defines if we wait for the start signal or not.
     * @return \Livekit\EgressInfo
     *                             The egress info.
     */
    public function startWebEgress(
        string $url,
        EncodedFileOutput|EncodedOutputs|ImageOutput|SegmentedFileOutput|StreamOutput $output,
        null|EncodingOptions|EncodingOptionsPreset $options = null,
        bool $audioOnly = false,
        bool $videoOnly = false,
        bool $awaitStartSignal = false
    ): EgressInfo {
        [
            $file,
            $stream,
            $segments,
            $preset,
            $advanced,
            $fileOutputs,
            $streamOutputs,
            $segmentOutputs,
            $imageOutputs,
        ] = $this->getOutputParams($output, $options);

        // Set the request data.
        $data = [
            'url' => $url,
            'audio_only' => $audioOnly,
            'video_only' => $videoOnly,
            'await_start_signal' => $awaitStartSignal,
        ];

        $data += array_filter([
            'file_outputs' => $fileOutputs,
            'stream_outputs' => $streamOutputs,
            'segment_outputs' => $segmentOutputs,
            'image_outputs' => $imageOutputs,
        ]);

        if ($file) {
            $data['file'] = $file;
        } elseif ($segments) {
            $data['segments'] = $segments;
        } elseif ($stream) {
            $data['stream'] = $stream;
        }

        if ($preset) {
            $data['preset'] = $preset;
        } else {
            $data['advanced'] = $advanced;
        }

        $videoGrant = new VideoGrant;
        $videoGrant->setRoomRecord();

        return $this->rpc->StartWebEgress(
            $this->authHeader($videoGrant),
            new WebEgressRequest($data)
        );
    }

    /**
     * Starts a track composite egress with up to one audio and one video track.
     *
     * Track IDs can be found using webhooks or one of the server SDKs.
     *
     * @param  string  $roomName
     *                            The name of the room.
     * @param  \Agence104\LiveKit\EncodedOutputs|\Livekit\EncodedFileOutput|\Livekit\StreamOutput|\Livekit\SegmentedFileOutput|\Livekit\ImageOutput  $output
     *                                                                                                                                                        The file or stream output.
     * @param  string  $audioTrackId
     *                                The audio track id.
     * @param  string  $videoTrackId
     *                                The video track id.
     * @param  \Livekit\EncodingOptionsPreset|\Livekit\EncodingOptions|null  $options
     *                                                                                 The encoding options or preset.
     * @return \Livekit\EgressInfo
     *                             The egress info.
     */
    public function startTrackCompositeEgress(
        string $roomName,
        EncodedFileOutput|EncodedOutputs|ImageOutput|SegmentedFileOutput|StreamOutput $output,
        string $audioTrackId = '',
        string $videoTrackId = '',
        null|EncodingOptions|EncodingOptionsPreset $options = null,
    ): EgressInfo {
        [
            $file,
            $stream,
            $segments,
            $preset,
            $advanced,
            $fileOutputs,
            $streamOutputs,
            $segmentOutputs,
            $imageOutputs,
        ] = $this->getOutputParams($output, $options);

        // Set the request data.
        $data = [
            'room_name' => $roomName,
            'audio_track_id' => $audioTrackId,
            'video_track_id' => $videoTrackId,
        ];

        $data += array_filter([
            'file_outputs' => $fileOutputs,
            'stream_outputs' => $streamOutputs,
            'segment_outputs' => $segmentOutputs,
            'image_outputs' => $imageOutputs,
        ]);

        if ($file) {
            $data['file'] = $file;
        } elseif ($segments) {
            $data['segments'] = $segments;
        } elseif ($stream) {
            $data['stream'] = $stream;
        }

        if ($preset) {
            $data['preset'] = $preset;
        } else {
            $data['advanced'] = $advanced;
        }

        $videoGrant = new VideoGrant;
        $videoGrant->setRoomRecord();

        return $this->rpc->StartTrackCompositeEgress(
            $this->authHeader($videoGrant),
            new TrackCompositeEgressRequest($data)
        );
    }

    /**
     * Starts a track egress.
     *
     * Track ID can be found using webhooks or one of the server SDKs.
     *
     * @param  string  $roomName
     *                            The name of the room.
     * @param  \Livekit\DirectFileOutput|string  $output
     *                                                    The file or websocket output.
     * @param  string  $trackId
     *                           The track id.
     * @return \Livekit\EgressInfo
     *                             The egress info.
     */
    public function startTrackEgress(
        string $roomName,
        DirectFileOutput|string $output,
        string $trackId
    ): EgressInfo {
        // Set the request data.
        $data = [
            'room_name' => $roomName,
            'track_id' => $trackId,
        ];
        ($output instanceof DirectFileOutput && ! empty($output->getFilepath()))
          ? $data['file'] = $output
          : $data['websocket_url'] = $output;

        $videoGrant = new VideoGrant;
        $videoGrant->setRoomRecord();

        return $this->rpc->StartTrackEgress(
            $this->authHeader($videoGrant),
            new TrackEgressRequest($data)
        );
    }

    /**
     * Updates the web layout of an active RoomCompositeEgress.
     *
     * @param  string  $egressId
     *                            The egress id.
     * @param  string  $layout
     *                          The egress layout.
     * @return \Livekit\EgressInfo
     *                             The egress info.
     */
    public function updateLayout(
        string $egressId,
        string $layout
    ): EgressInfo {
        $videoGrant = new VideoGrant;
        $videoGrant->setRoomRecord();

        return $this->rpc->UpdateLayout(
            $this->authHeader($videoGrant),
            new UpdateLayoutRequest([
                'egress_id' => $egressId,
                'layout' => $layout,
            ])
        );
    }

    /**
     * Adds or removes stream urls from an active stream.
     *
     * @param  string  $egressId
     *                            The egress id.
     * @param  array  $addOutputUrls
     *                                The output Urls to add to the active stream.
     * @param  array  $removeOutputUrls
     *                                   The output Urls to remove from the active stream.
     * @return \Livekit\EgressInfo
     *                             The egress info.
     */
    public function updateStream(
        string $egressId,
        array $addOutputUrls = [],
        array $removeOutputUrls = []
    ): EgressInfo {
        $videoGrant = new VideoGrant;
        $videoGrant->setRoomRecord();

        return $this->rpc->UpdateStream(
            $this->authHeader($videoGrant),
            new UpdateStreamRequest([
                'egress_id' => $egressId,
                'add_output_urls' => $addOutputUrls,
                'remove_output_urls' => $removeOutputUrls,
            ])
        );
    }

    /**
     * Gets the list of active egress. Does not include completed egress.
     *
     * @param  string  $roomName
     *                            The name of the room.
     * @param  string  $egressId
     *                            Optional, filter by an egress ID.
     * @param  bool  $active
     *                        Optional, list active egress only.
     * @return \Livekit\ListEgressResponse
     *                                     The list of egress.
     */
    public function listEgress(
        string $roomName = '',
        string $egressId = '',
        bool $active = false
    ): ListEgressResponse {
        $videoGrant = new VideoGrant;
        $videoGrant->setRoomRecord();

        return $this->rpc->ListEgress(
            $this->authHeader($videoGrant),
            new ListEgressRequest([
                'room_name' => $roomName,
                'egress_id' => $egressId,
                'active' => $active,
            ])
        );
    }

    /**
     * Stops an active egress.
     *
     * @param  string  $egressId
     *                            The egress id.
     * @return \Livekit\EgressInfo
     *                             The egress info.
     */
    public function stopEgress(string $egressId): EgressInfo
    {
        $videoGrant = new VideoGrant;
        $videoGrant->setRoomRecord();

        return $this->rpc->StopEgress(
            $this->authHeader($videoGrant),
            new StopEgressRequest([
                'egress_id' => $egressId,
            ])
        );
    }
}
