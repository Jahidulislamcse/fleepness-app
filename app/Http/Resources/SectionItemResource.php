<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\SectionItem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SectionItem
 */
class SectionItemResource extends JsonResource
{
    #[\Override]
    public function toArray($request)
    {
        $tagId = $this->tag ? $this->tag->id : null;

        $products = collect();

        if (1 === $this->show_products && $tagId) {
            $products = Product::with('images')
                ->whereNull('deleted_at')
                ->where('status', 'active')->latest()
                ->take(12)
                ->get()
                ->filter(function ($product) use ($tagId): bool {
                    $tags = $product->tags;

                    return in_array($tagId, $tags);
                })
                ->values();
        }

        return [
            $this->getKeyName() => $this->getkey(),
            'image' => $this->image,
            'title' => $this->title,
            'bio' => $this->bio,
            'tag_id' => $tagId,
            'tag_name' => $this->tag ? $this->tag->name : null,
            'index' => $this->index,
            'visibility' => (bool) $this->visibility,
            'products' => $products->map(function ($product): array {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'selling_price' => $product->selling_price,
                    'discount_price' => $product->discount_price,
                    'images' => \App\Models\ProductImage::query()->where('product_id', $product->id)
                        ->get(['id', 'path', 'alt_text']),
                    'sizes' => \App\Models\ProductSize::query()->where('product_id', $product->id)
                        ->get(['id', 'size_name', 'size_value']),
                ];
            }),
        ];
    }
}
