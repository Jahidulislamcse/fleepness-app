<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $formatted_address
 * @property string $street
 * @property string $city
 * @property string $label
 * @property string $postal_code
 * @property string $country
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereFormattedAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereUserId($value)
 */
	class Address extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $amount
 * @property string|null $transaction_id
 * @property string|null $payment_media
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill wherePaymentMedia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bill whereUserId($value)
 */
	class Bill extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int $quantity
 * @property bool $selected
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $size_id
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\ProductSize|null $size
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereSelected($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereSizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereUserId($value)
 */
	class CartItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $store_title
 * @property string|null $profile_img
 * @property string|null $cover_img
 * @property string $slug
 * @property string|null $description
 * @property string $status
 * @property int|null $order
 * @property string|null $image
 * @property string|null $mark
 * @property int|null $parent_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Slider> $sliders
 * @property-read int|null $sliders_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCoverImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereMark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereProfileImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereStoreTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $minutes
 * @property string $fee
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryModel whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryModel whereMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeliveryModel whereUpdatedAt($value)
 */
	class DeliveryModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DeviceToken whereUserId($value)
 */
	class DeviceToken extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $vat
 * @property string $platform_fee
 * @property string $commission
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereCommission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee wherePlatformFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereVat($value)
 */
	class Fee extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $follower_id
 * @property int $vendor_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $follower
 * @property-read \App\Models\User $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follower newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follower newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follower query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follower whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follower whereFollowerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follower whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follower whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follower whereVendorId($value)
 */
	class Follower extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property int $vendor_id
 * @property string|null $total_duration
 * @property \Illuminate\Support\Carbon|null $scheduled_time
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $egress_id
 * @property int $total_participants
 * @property array<array-key, mixed>|null $egress_data
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LivestreamComment> $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LivestreamLike> $likes
 * @property-read int|null $likes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LivestreamProduct> $livestreamProducts
 * @property-read int|null $livestream_products_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $participants
 * @property-read int|null $participants_count
 * @property-read \App\Models\LivestreamProduct|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read mixed $recordings
 * @property-read mixed $room_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LivestreamSave> $saves
 * @property-read int|null $saves_count
 * @property-read mixed $short_videos
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\ModelStatus\Status> $statuses
 * @property-read int|null $statuses_count
 * @property-read mixed $thumbnails
 * @property-read \App\Models\User|null $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream currentStatus(...$names)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream otherCurrentStatus(...$names)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream whereEgressData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream whereEgressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream whereScheduledTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream whereTotalDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream whereTotalParticipants($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Livestream whereVendorId($value)
 */
	class Livestream extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $livestream_id
 * @property string $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Livestream $livestream
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamComment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamComment whereLivestreamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamComment whereUserId($value)
 */
	class LivestreamComment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $livestream_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Livestream $livestream
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamLike newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamLike newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamLike query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamLike whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamLike whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamLike whereLivestreamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamLike whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamLike whereUserId($value)
 */
	class LivestreamLike extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LivestreamProduct
 *
 * @property int $product_id
 * @property int $livestream_id
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct whereLivestreamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LivestreamProduct whereProductId($value)
 * @mixin \Eloquent
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamProduct whereUpdatedAt($value)
 */
	class LivestreamProduct extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $livestream_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Livestream $livestream
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamSave newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamSave newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamSave query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamSave whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamSave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamSave whereLivestreamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamSave whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivestreamSave whereUserId($value)
 */
	class LivestreamSave extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $message
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUserId($value)
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $order_code
 * @property int|null $is_multi_seller
 * @property int|null $total_sellers
 * @property string|null $delivery_model
 * @property string|null $product_cost
 * @property string|null $commission
 * @property string|null $delivery_fee
 * @property string|null $platform_fee
 * @property string|null $vat
 * @property string|null $grand_total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SellerOrder> $sellerOrders
 * @property-read int|null $seller_orders_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCommission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereIsMultiSeller($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePlatformFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereProductCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalSellers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereVat($value)
 */
	class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $icon
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereUpdatedAt($value)
 */
	class PaymentMethod extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $category_id
 * @property array<array-key, mixed>|null $tags
 * @property int $size_template_id
 * @property string $name
 * @property string $slug
 * @property string $code
 * @property int|null $quantity
 * @property int|null $order_count
 * @property float|null $selling_price
 * @property float|null $discount_price
 * @property string|null $short_description
 * @property string|null $long_description
 * @property string|null $deleted_at
 * @property string|null $status
 * @property string $admin_approval
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\ProductImage|null $firstImage
 * @property-read mixed $image_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductImage> $images
 * @property-read int|null $images_count
 * @property-read \App\Models\ProductImage|null $imagesProduct
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Livestream> $livestreams
 * @property-read int|null $livestreams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductReview> $reviews
 * @property-read int|null $reviews_count
 * @property-read \App\Models\SizeTemplate $sizeTemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductSize> $sizes
 * @property-read int|null $sizes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Stock> $stocks
 * @property-read int|null $stocks_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereAdminApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDiscountPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereLongDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereOrderCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSellingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSizeTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUserId($value)
 */
	class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $product_id
 * @property string $path
 * @property string|null $alt_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereUpdatedAt($value)
 */
	class ProductImage extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $product_id
 * @property int|null $user_id
 * @property int $rating
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReview whereUserId($value)
 */
	class ProductReview extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $product_id
 * @property string $size_name
 * @property string $size_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSize newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSize newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSize query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSize whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSize whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSize whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSize whereSizeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSize whereSizeValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSize whereUpdatedAt($value)
 */
	class ProductSize extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $section_name
 * @property string|null $section_type
 * @property string|null $section_title
 * @property int $category_id
 * @property int|null $index
 * @property int $visibility
 * @property string|null $background_image
 * @property string|null $banner_image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $bio
 * @property string $placement_type
 * @property int|null $cat_index
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SectionItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereBannerImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereCatIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section wherePlacementType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereSectionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereSectionTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereSectionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereVisibility($value)
 */
	class Section extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $section_id
 * @property string|null $image
 * @property string|null $title
 * @property string|null $bio
 * @property string|null $tag_id
 * @property int|null $index
 * @property int $visibility
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Section $section
 * @property-read \App\Models\Category|null $tag
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionItem whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionItem whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionItem whereIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionItem whereSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionItem whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionItem whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionItem whereVisibility($value)
 */
	class SectionItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read User $seller
 * @property int $id
 * @property int $order_id
 * @property int $seller_id
 * @property \App\Enums\SellerOrderStatus|null $status
 * @property string|null $status_message
 * @property \Illuminate\Support\Carbon|null $delivery_start_time
 * @property \Illuminate\Support\Carbon|null $delivery_end_time
 * @property string|null $product_cost
 * @property string|null $commission
 * @property string|null $balance
 * @property bool|null $rider_assigned
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SellerOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Order $order
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder whereCommission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder whereDeliveryEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder whereDeliveryStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder whereProductCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder whereRiderAssigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder whereSellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder whereStatusMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrder whereUpdatedAt($value)
 */
	class SellerOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $seller_order_id
 * @property int $product_id
 * @property string|null $size
 * @property int|null $quantity
 * @property string|null $total_cost
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\SellerOrder $sellerOrder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrderItem whereSellerOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrderItem whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrderItem whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerOrderItem whereUpdatedAt($value)
 */
	class SellerOrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $vendor_id
 * @property array<array-key, mixed> $tags
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerTags newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerTags newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerTags query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerTags whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerTags whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerTags whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerTags whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SellerTags whereVendorId($value)
 */
	class SellerTags extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $logo
 * @property string $favicon
 * @property string $address
 * @property string $phone
 * @property string $email
 * @property string|null $meta_keyword
 * @property string|null $meta_description
 * @property string $footer_logo
 * @property string $footer_text
 * @property string $footer_copyright_by
 * @property string $footer_copyright_url
 * @property string $footer_bg_image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $num_of_tag
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFavicon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFooterBgImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFooterCopyrightBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFooterCopyrightUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFooterLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFooterText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereMetaKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereNumOfTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 */
	class Setting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCategory whereUpdatedAt($value)
 */
	class ShopCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $video
 * @property string|null $alt_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShortVideo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShortVideo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShortVideo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShortVideo whereAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShortVideo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShortVideo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShortVideo whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShortVideo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShortVideo whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShortVideo whereVideo($value)
 */
	class ShortVideo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $seller_id
 * @property string $template_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SizeTemplateItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplate whereSellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplate whereTemplateName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplate whereUpdatedAt($value)
 */
	class SizeTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $template_id
 * @property string $size_name
 * @property string $size_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SizeTemplate|null $template
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplateItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplateItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplateItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplateItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplateItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplateItem whereSizeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplateItem whereSizeValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplateItem whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SizeTemplateItem whereUpdatedAt($value)
 */
	class SizeTemplateItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $photo
 * @property string|null $photo_alt
 * @property string|null $title
 * @property int|null $category_id
 * @property int|null $tag_id
 * @property string|null $description
 * @property string|null $btn_name
 * @property string|null $btn_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Category|null $tag
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider whereBtnName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider whereBtnUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider wherePhotoAlt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Slider whereUpdatedAt($value)
 */
	class Slider extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $product_id
 * @property string|null $size
 * @property int|null $quantity
 * @property int $order_qty
 * @property string|null $buying_price
 * @property string|null $selling_price
 * @property string|null $discount_price
 * @property string|null $photo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock whereBuyingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock whereDiscountPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock whereOrderQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock whereSellingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stock whereUpdatedAt($value)
 */
	class Stock extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $name
 * @property string|null $shop_name
 * @property int|null $shop_category
 * @property string|null $email
 * @property string|null $phone_number
 * @property string|null $otp
 * @property string|null $otp_expires_at
 * @property string|null $banner_image
 * @property string|null $cover_image
 * @property string|null $pickup_location
 * @property string|null $description
 * @property string $role
 * @property string|null $status
 * @property int|null $order_count
 * @property string $total_sales
 * @property string $balance
 * @property string $withdrawn_amount
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $provider
 * @property string|null $provider_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DeviceToken> $deviceTokens
 * @property-read int|null $device_tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LivestreamLike> $likedLivestreams
 * @property-read int|null $liked_livestreams_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserPayment> $payments
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VendorReview> $reviews
 * @property-read int|null $reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LivestreamSave> $savedLivestreams
 * @property-read int|null $saved_livestreams_count
 * @property-read \App\Models\ShopCategory|null $shopCategory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBannerImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOrderCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOtpExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePickupLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShopCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereShopName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTotalSales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereWithdrawnAmount($value)
 */
	class User extends \Eloquent implements \App\Support\Notification\Contracts\FcmNotifiableByDevice {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $payment_method_id
 * @property string $account_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PaymentMethod $paymentMethod
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPayment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPayment whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPayment wherePaymentMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPayment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPayment whereUserId($value)
 */
	class UserPayment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property int $vendor_id
 * @property int $rating
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\User $vendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorReview newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorReview newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorReview query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorReview whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorReview whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorReview whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorReview whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorReview whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorReview whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VendorReview whereVendorId($value)
 */
	class VendorReview extends \Eloquent {}
}

namespace App\Webhooks\Livekit{
/**
 * App\Webhooks\Livekit\LivekitWebhookCall
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LivekitWebhookCall newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LivekitWebhookCall newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LivekitWebhookCall query()
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property string $url
 * @property array<array-key, mixed>|null $headers
 * @property array<array-key, mixed>|null $payload
 * @property array<array-key, mixed>|null $exception
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivekitWebhookCall whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivekitWebhookCall whereException($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivekitWebhookCall whereHeaders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivekitWebhookCall whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivekitWebhookCall whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivekitWebhookCall wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivekitWebhookCall whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LivekitWebhookCall whereUrl($value)
 */
	class LivekitWebhookCall extends \Eloquent {}
}

