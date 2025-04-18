<?php

namespace App\Models;

use App\Constants\LivestreamStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\ModelStatus\HasStatuses;

/**
 * App\Models\Livestream
 *
 * @property int $id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\ModelStatus\Status> $statuses
 * @property-read int|null $statuses_count
 * @property-read string $status
 * @property-read \App\Models\Vendor|null $vendor
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream currentStatus(...$names)
 * @method static \Database\Factories\LivestreamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream otherCurrentStatus(...$names)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream query()
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereUpdatedAt($value)
 *
 * @property int $vendor_id
 * @property \Illuminate\Support\Carbon|null $scheduled_time
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property int|null $total_duration
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereScheduledTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereTotalDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Livestream whereVendorId($value)
 *
 * @mixin \Eloquent
 */
class Livestream extends Model implements HasMedia
{
    use HasFactory, HasStatuses, InteractsWithMedia;

    protected $fillable = ['title', 'vendor_id', 'total_duration', 'scheduled_time', 'started_at', 'ended_at'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'scheduled_time' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class);
    }

    // public function products()
    // {
    //     return $this->belongsToMany(Product::class)->using(LivestreamProduct::class);
    // }

    public static function booted(): void
    {
        static::created(function (Livestream $livestream) {
            $livestream->setStatus(LivestreamStatuses::INITIAL->value);
        });
    }

    public function getRoomName(): string
    {
        $vendorId = $this->vendor_id;
        $livestreamId = $this->getKey();
        $ownerId = $this->vendor->user->getKey();

        return "room-{$ownerId}-{$vendorId}-{$livestreamId}";
    }
}
