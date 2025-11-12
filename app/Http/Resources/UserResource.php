<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 *
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            $this->getKeyName() => $this->getKey(),
            'name' => $this->name,
            'shop_name' => $this->shop_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'notification_channel' => $this->receivesBroadcastNotificationsOn(),
            'banner_image' => $this->banner_image,
            'cover_image' => $this->cover_image,
            'pickup_location' => $this->pickup_location,
            'description' => $this->description,
            'role' => $this->role,
            'status' => $this->status,
            'order_count' => $this->order_count,
            'total_sales' => $this->total_sales,
            'balance' => $this->balance,
            'withdrawn_amount' => $this->withdrawn_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'reviews' => $this->whenLoaded('reviews', fn () => VendorReviewResource::collection($this->reviews)),
            'payments' => $this->whenLoaded('payments', fn () => UserPaymentResource::collection($this->payments)),
            'shop_category' => $this->whenLoaded('shopCategory', fn () => ShopCategoryResource::make($this->shopCategory)),
            'liked_livestreams' => $this->whenLoaded('likedLivestreams', fn () => LivestreamLikeResource::collection($this->likedLivestreams)),
            'saved_livestreams' => $this->whenLoaded('savedLivestreams', fn () => LivestreamSaveResource::collection($this->savedLivestreams)),
        ];
    }
}
