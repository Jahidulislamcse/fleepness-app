<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShortsProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_description' => $this->short_description,
            'selling_price' => $this->selling_price,
            'discount_price' => $this->discount_price,
            'images' => $this->images->map(fn($img) => asset($img->path)),
        ];
    }
}
