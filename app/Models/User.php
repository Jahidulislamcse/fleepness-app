<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Support\Notification\Contracts\SupportsFcmChannel;
use App\Support\Notification\Contracts\FcmNotifiableByDevice;
use App\Support\Notification\Contracts\FcmBroadcastNotifiableByDevice;

class User extends Authenticatable implements FcmBroadcastNotifiableByDevice, FcmNotifiableByDevice
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function routeBroadcastNotificationForFcmTokens(): null|array|string
    {
        return $this->deviceTokens->pluck('token')->toArray();
    }

    public function removeDeviceToken(array|string $token): mixed
    {
        $tokens = Arr::wrap($token);

        return $this
            ->deviceTokens()
            ->whereIn('token', $tokens)
            ->delete();
    }

    public function routeNotificationForFcmTokens(Notification&SupportsFcmChannel $notification): null|array|string
    {
        return $this->deviceTokens->pluck('token')->toArray();
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail_pictures')
            ->useDisk('public'); // You can use a different disk if needed
    }

    protected function coverImage(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? Storage::url($value) : null);
    }

    protected function bannerImage(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? Storage::url($value) : null);
    }

    public function reviews()
    {
        return $this->hasMany(VendorReview::class);
    }

    public function shopCategory()
    {
        return $this->belongsTo(ShopCategory::class, 'shop_category');
        // 'shop_category' is the foreign key in users table
    }

    public function payments()
    {
        return $this->hasMany(UserPayment::class);
    }

    /**
     * @return HasMany<Livestream,$this>
     */
    public function livestreams(): HasMany
    {
        return $this->hasMany(Livestream::class, 'vendor_id');
    }

    public function likedLivestreams()
    {
        return $this->hasMany(LivestreamLike::class);
    }

    public function savedLivestreams()
    {
        return $this->hasMany(LivestreamSave::class);
    }
}
