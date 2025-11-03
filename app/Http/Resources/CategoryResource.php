<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
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
            'store_title' => $this->store_title,
            'profile_img' => $this->profile_img,
            'cover_img' => $this->cover_img,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'order' => $this->order,
            'image' => $this->image,
            'mark' => $this->mark,

            'sliders' => $this->whenLoaded('sliders', fn () => SliderResource::collection($this->sliders)),
            'children' => $this->whenLoaded('children', fn () => CategoryResource::collection($this->children)),
            'parent' => $this->whenLoaded('parent', fn () => CategoryResource::make($this->parent)),
            'grand_parent' => $this->whenLoaded('grandParent', fn () => CategoryResource::make($this->grandParent)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
