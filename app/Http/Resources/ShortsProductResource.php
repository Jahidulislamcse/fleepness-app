<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'code' => $this->code,
            'quantity' => $this->quantity,
            'selling_price' => $this->selling_price,
            'discount_price' => $this->discount_price,
            'short_description' => $this->short_description,
            'reviews' => $this->reviews,
            'time' => $this->time,
            'discount' => $this->discount,

            'images' => $this->images->map(fn ($img) =>
                str_starts_with($img->path, 'http')
                    ? $img->path
                    : Storage::url($img->path)
            ),

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'banner_image' => $this->user?->banner_image,
                'cover_image' => $this->user?->cover_image,
            ],

            'sizes' => $this->sizes->map(fn ($size) => [
                'id' => $size->id,
                'size_name' => $size->size_name,
                'size_value' => $size->size_value,
            ]),
        ];
    }
}

