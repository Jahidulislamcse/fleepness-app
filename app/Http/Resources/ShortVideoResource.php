<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShortVideoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'video' => $this->video, 
            'likes_count' => $this->likes_count ?? 0,
            'products' => $this->products->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'short_description' => $product->short_description,
                'images' => $product->images->map(fn($img) => asset($img->path)),
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
