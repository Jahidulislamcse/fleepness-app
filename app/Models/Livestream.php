<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Broadcasting\Channel;
use App\Constants\LivestreamStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use App\Http\Resources\LivestreamResource;
use Illuminate\Notifications\Notification;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Support\Notification\Contracts\SupportsFcmChannel;
use App\Support\Notification\Contracts\FcmNotifiableByTopic;
use Illuminate\Database\Eloquent\BroadcastsEventsAfterCommit;

/**
 * @property-read string $room_name
 * @property-read list<array{filenamePrefix:string,imageCount:int,startedAt:int,endedAt:int}>|null $thumbnails
 * @property-read list<array{filename:string,startedAt:int,endedAt:int,duration:int,size:int,location:string}>|null $recordings
 * @property-read list<array{playlistName:string,livePlaylistName:string,duration:int,size:int,playlistLocation:string,livePlaylistLocation:string,segmentCount:int,startedAt:int,endedAt:int}>|null $short_videos
 */
class Livestream extends Model implements FcmNotifiableByTopic, HasMedia
{
    use BroadcastsEventsAfterCommit, HasFactory, InteractsWithMedia, Notifiable;

    protected $fillable = ['title', 'vendor_id', 'total_duration', 'scheduled_time', 'started_at', 'ended_at', 'egress_id', 'egress_data', 'room_id'];

    protected $casts = [
        'ended_at' => 'datetime',
        'egress_data' => 'json',
        'started_at' => 'datetime',
        'scheduled_time' => 'datetime',
        'status' => LivestreamStatuses::class,
    ];

    protected $hidden = [
        'room_id',
        'egress_data',
    ];

    protected $appends = [
        'room_name',
    ];

    public function routeNotificationForFcmTopics(Notification&SupportsFcmChannel $notification): null|array|string
    {
        return $this->room_name;
    }

    /**
     * @return BelongsTo<User,$this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * @return BelongsToMany<Product,$this,LivestreamProduct>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->using(LivestreamProduct::class);
    }

    /**
     * @return HasMany<LivestreamProduct,$this>
     */
    public function livestreamProducts(): HasMany
    {
        return $this->hasMany(LivestreamProduct::class);
    }

    /**
     * @return BelongsToMany<User,$this>
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,          // Related model
            'livestream_user',    // Pivot table
            'livestream_id',      // Foreign key on pivot referencing livestreams
            'participant_id'      // Foreign key on pivot referencing users
        );
    }

    /**
     * @return HasMany<LivestreamComment,$this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(LivestreamComment::class);
    }

    /**
     * @return HasMany<LivestreamLike,$this>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(LivestreamLike::class);
    }

    /**
     * @return HasMany<LivestreamSave,$this>
     */
    public function saves(): HasMany
    {
        return $this->hasMany(LivestreamSave::class);
    }

    protected function roomName(): Attribute
    {
        return Attribute::get($this->getRoomName(...))->shouldCache();
    }

    public function getRoomName(): string
    {
        return sprintf('livestream_%s', $this->getKey());
    }

    public function getEncodedFileOutputName(): string
    {
        $title = $this->title;

        return sprintf('%s_%s', $title, today()->format('Ymd_h_i_s'));
    }

    public function stopRecording(): void
    {
        $this->ended_at = now();

        if (filled($egressId = $this->egress_id)) {
            \App\Facades\Livestream::stopRecording($egressId);
        }
    }

    public function startRecording(): void
    {
        $this->started_at = now();
        $egress = \App\Facades\Livestream::startRecording($this->getRoomName(), $this->getEncodedFileOutputName());
        $this->fill([
            'room_id' => $egress->getRoomId(),
            'egress_id' => $egress->getEgressId(),
        ]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->singleFile();
    }

    protected function recordings(): Attribute
    {
        return Attribute::get(function () {
            $egressData = $this->egress_data;

            $recordings = data_get($egressData, 'recordings', []);

            return collect($recordings)->map(function (array $egressInfo): array {
                $filename = data_get($egressInfo, 'filename', '');

                $location = Storage::disk('r2')->url($filename);

                return [
                    ...$egressInfo,
                    'location' => $location,
                ];
            })->all();
        })->shouldCache();
    }

    protected function shortVideos(): Attribute
    {
        return Attribute::get(function () {
            $egressData = $this->egress_data;

            $recordings = data_get($egressData, 'short_videos', []);

            return collect($recordings)->map(function (array $egressInfo): array {
                $playlistName = data_get($egressInfo, 'playlistName', '');

                $playlistLocation = Storage::disk('r2')->url($playlistName);

                return [
                    ...$egressInfo,
                    'playlistLocation' => $playlistLocation,
                ];
            })->all();
        })->shouldCache();
    }

    protected function thumbnails(): Attribute
    {
        return Attribute::get(function () {
            $egressData = $this->egress_data;

            $recordings = data_get($egressData, 'thumbnails', []);

            return collect($recordings)->map(function (array $egressInfo): array {
                $filenamePrefix = data_get($egressInfo, 'filenamePrefix', '');

                $directoryName = str($filenamePrefix)->dirname();

                $thumbnails = Storage::disk('r2')->files($directoryName);

                $thumbnails = collect($thumbnails)
                    ->filter(function ($thumbnailPath): bool {
                        $ext = strtolower(pathinfo($thumbnailPath, PATHINFO_EXTENSION));

                        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                    })->map(fn ($thmnailPath) => Storage::disk('r2')->url($thmnailPath))
                    ->all();

                return [
                    ...$egressInfo,
                    'thumbnails' => $thumbnails,
                ];
            })->all();
        })->shouldCache();
    }

    /**
     * The channels the user receives notification broadcasts on.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return $this->getRoomName();
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model>
     */
    public function broadcastOn(string $event): array
    {
        return match ($event) {
            'created' => [new Channel('livestream_feed')],
            'updated' => [new Channel('livestream_feed'), new Channel($this->getRoomName())],
            default => []
        };
    }

    /**
     * The model event's broadcast name.
     */
    public function broadcastAs(string $event): ?string
    {
        return sprintf('livestream_%s', $event);
    }

    /**
     * Get the data to broadcast for the model.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(string $event): array
    {
        return $this
            ->toResource(LivestreamResource::class)
            ->resolve();
    }
}
