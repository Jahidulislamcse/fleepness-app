<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Product $resource
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            $this->getKeyName() => $this->getKey(),

            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'quantity' => $this->quantity,
            'order_count' => $this->order_count,
            'selling_price' => $this->selling_price,
            'discount_price' => $this->discount_price,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'deleted_at' => $this->deleted_at,
            'status' => $this->status,
            'admin_approval' => $this->admin_approval,
            'description' => $this->long_description,
            'reviews' => $this->reviews,
            'time' => $this->time,
            'discount' => $this->discount,

            $this->mergeWhen($this->relationLoaded('tag'), fn () => [
                'tag' => CategoryResource::make($this->tag)->asTag(),
                $this->mergeWhen($this->tag->relationLoaded('grandParent'), fn () => [
                    'category' => CategoryResource::make($this->tag->grandParent),
                ]),
                $this->mergeWhen($this->tag->relationLoaded('parent'), fn () => [
                    'sub_category' => CategoryResource::make($this->tag->parent),
                ]),
            ]),

            'user' => $this->whenLoaded('user', fn () => UserResource::make($this->user)),
            'images' => $this->whenLoaded('images', fn () => ProductImageResource::collection($this->images)),
            'size_template' => $this->whenLoaded('sizeTemplate', fn () => SizeTemplateResource::make($this->sizeTemplate)),
            'sizes' => $this->whenLoaded('sizes', fn () => ProductSizeResource::collection($this->sizes)),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
