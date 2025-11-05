<?php

namespace App\Http\Resources;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductImage
 */
class ProductImageResource extends JsonResource
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
            'path' => $this->path,
            'alt_text' => $this->alt_text,
            'product' => $this->whenLoaded('product', fn () => ProductResource::make($this->product)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
