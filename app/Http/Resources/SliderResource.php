<?php

namespace App\Http\Resources;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Slider
 */
class SliderResource extends JsonResource
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
            'photo' => $this->photo,
            'photo_alt' => $this->photo_alt,
            'title' => $this->title,
            'category_id' => $this->category_id,
            'tag_id' => $this->tag_id,
            'description' => $this->description,
            'btn_name' => $this->btn_name,
            'btn_url' => $this->btn_url,
            'category' => $this->whenLoaded('category', fn () => CategoryResource::make($this->category)),
            'tag' => $this->whenLoaded('tag', fn () => CategoryResource::make($this->tag)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
