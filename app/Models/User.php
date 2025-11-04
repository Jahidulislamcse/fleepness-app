<?php

namespace App\Models;

use App\Enums\SellerStatus;
use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Stringable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use App\Notifications\LoginOtpNotification;
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'status' => SellerStatus::class,
        ];
    }

    public function removeDeviceToken(array|string $token): mixed
    {
        $tokens = Arr::wrap($token);

        return $this
            ->deviceTokens()
            ->whereIn('token', $tokens)
            ->delete();
    }

    public function receivesBroadcastNotificationsOn()
    {
        return sprintf('user_%s', $this->getKey());
    }

    public function routeBroadcastNotificationForFcmTokens(): null|array|string
    {
        return $this->deviceTokens->pluck('token')->toArray();
    }

    public function routeNotificationForFcmTokens(Notification&SupportsFcmChannel $notification): null|array|string
    {
        return $this->deviceTokens->pluck('token')->toArray();
    }

    public function routeNotificationForSms($notification = null)
    {
        return $this->phone_number;
    }

    protected function phoneNumber(): Attribute
    {
        return Attribute::get(function (?string $value) {
            if (empty($value)) {
                return null;
            }

            return str($value)
                ->pipe(function (Stringable $str) {
                    return $str->when($str->startsWith('0'))->prepend('88');
                })
                ->pipe(function (Stringable $str) {
                    return $str->unless($str->startsWith('880'))->prepend('880');
                })
                ->value();
        });
    }

    /**
     * Get all device tokens associated with the user.
     *
     * @return HasMany<DeviceToken,$this>
     */
    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function sendOtpNotification(int|string $otp)
    {
        $this->notify(new LoginOtpNotification($otp));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail_pictures')
            ->useDisk('public'); 
    }

    protected function coverImage(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? Storage::url($value) : null);
    }

    protected function bannerImage(): Attribute
    {
        return Attribute::get(fn ($value) => $value ? Storage::url($value) : null);
    }

    /**
     * Get all vendor reviews written for the user.
     *
     * @return HasMany<VendorReview,$this>
     */
    public function reviews()
    {
        return $this->hasMany(VendorReview::class);
    }

    /**
     * Get the shop category that the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<ShopCategory,$this>
     */
    public function shopCategory()
    {
        return $this->belongsTo(ShopCategory::class, 'shop_category');
    }

    /**
     * Get all payments associated with the user.
     *
     * @return HasMany<UserPayment,$this>
     */
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

    /**
     * Get all livestreams liked by the user.
     *
     * @return HasMany<LivestreamLike,$this>
     */
    public function likedLivestreams()
    {
        return $this->hasMany(LivestreamLike::class);
    }

    /**
     * Get all livestreams saved by the user.
     *
     * @return HasMany<LivestreamSave,$this>
     */
    public function savedLivestreams()
    {
        return $this->hasMany(LivestreamSave::class);
    }

    public function getOtpCacheKey()
    {
        return "otp_$this->phone_number";
    }

    public function getCachedOtp(mixed $default = null)
    {
        return cache()->get($this->getOtpCacheKey(), $default);
    }

    public function cacheOtpFor10Minutes(string $otp)
    {
        return cache()->put($this->getOtpCacheKey(), $otp, now()->addMinutes(10));
    }

    public function forgetCachedOtp()
    {
        return cache()->forget($this->getOtpCacheKey());
    }
}
