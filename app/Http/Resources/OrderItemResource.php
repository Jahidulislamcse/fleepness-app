<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'selling_price' => $this->product->selling_price,
                'discount_price' => $this->product->discount_price,
                'images' => $this->product->images->map(fn($img) => [
                    'id' => $img->id,
                    'path' => $img->path,
                ]),
            ],
            'size' => $this->size,
            'quantity' => $this->quantity,
            'total_cost' => $this->total_cost,
        ];
    }
}
