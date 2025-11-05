<?php

namespace App\Http\Resources;

use App\Models\ProductSize;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductSize
 */
class ProductSizeResource extends JsonResource
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
            'product' => $this->whenLoaded('product', fn () => ProductResource::make($this->product)),
            'size_name' => $this->size_name,
            'size_value' => $this->size_value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
