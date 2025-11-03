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

            $this->mergeWhen($this->relationLoaded('tag'), fn () => [
                'tag' => CategoryResource::make($this->tag),
                'tag_name' => $this->tag->name,
                $this->mergeWhen($this->tag->relationLoaded('grandParent'), fn () => [
                    'category_name' => $this->tag->grandParent->name,
                    'category' => CategoryResource::make($this->tag->grandParent),
                ]),
            ]),

            'user' => $this->whenLoaded('user', UserResource::make($this->user)),
            'size_template' => $this->whenLoaded('sizeTemplate', SizeTemplateResource::make($this->sizeTemplate)),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
