<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\ModelStatus\HasStatuses;
use App\Constants\LivestreamStatuses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Support\Notification\Contracts\SupportsFcmChannel;
use App\Support\Notification\Contracts\FcmNotifiableByTopic;

/**
 * @property-read string $status
 */
class Livestream extends Model implements FcmNotifiableByTopic, HasMedia
{
    use HasFactory, HasStatuses, InteractsWithMedia, Notifiable;

    protected $fillable = ['title', 'vendor_id', 'total_duration', 'scheduled_time', 'started_at', 'ended_at', 'egress_id', 'egress_data', 'room_id'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'scheduled_time' => 'datetime',
        'egress_data' => 'json',
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

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->using(LivestreamProduct::class);
    }

    public function livestreamProducts()
    {
        return $this->hasMany(LivestreamProduct::class);
    }

    public static function booted(): void
    {
        static::created(function (Livestream $livestream) {
            $livestream->setStatus(LivestreamStatuses::INITIAL->value);
        });
    }

    public function participants()
    {
        return $this->belongsToMany(
            User::class,          // Related model
            'livestream_user',    // Pivot table
            'livestream_id',      // Foreign key on pivot referencing livestreams
            'participant_id'      // Foreign key on pivot referencing users
        );
    }

    protected function roomName(): Attribute
    {
        return Attribute::get(function () {
            return $this->getRoomName();
        })->shouldCache();
    }

    public function getRoomName(): string
    {
        return sprintf('%s_%s', Str::snake($this->title), $this->getKey());
    }

    public function getEncodedFileOutputName(): string
    {
        $title = $this->title;

        return sprintf('%s_%s', $title, today()->format('Ymd_h_i_s'));
    }

    public function stopRecording()
    {
        $this->ended_at = now();

        if (filled($egressId = $this->egress_id)) {
            \App\Facades\Livestream::stopRecording($egressId);
        }
    }

    public function startRecording()
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

            return collect($recordings)->map(function (array $egressInfo) {
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

            return collect($recordings)->map(function (array $egressInfo) {
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

            return collect($recordings)->map(function (array $egressInfo) {
                $filenamePrefix = data_get($egressInfo, 'filenamePrefix', '');

                $directoryName = str($filenamePrefix)->dirname();

                $thumbnails = Storage::disk('r2')->files($directoryName);

                $thumbnails = collect($thumbnails)
                    ->filter(function ($thumbnailPath) {
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

    public function comments()
    {
        return $this->hasMany(LivestreamComment::class);
    }

    public function likes()
    {
        return $this->hasMany(LivestreamLike::class);
    }

    public function saves()
    {
        return $this->hasMany(LivestreamSave::class);
    }
}
