<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Product;
use App\Models\ProductImage;

class SectionItemResource extends JsonResource
{
    public function toArray($request)
    {
        $tagId = $this->tag ? $this->tag->id : null;

        $products = collect();

        if ($this->show_products == 1 && $tagId) {
            $products = Product::with('images')
                ->whereNull('deleted_at')
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->take(12)
                ->get()
                ->filter(function ($product) use ($tagId) {
                    $tags = json_decode($product->tags, true) ?: [];
                    return in_array($tagId, $tags);
                })
                ->values();
        }

        return [
            'id' => $this->id,
            'image' => $this->image ? asset($this->image) : null,
            'title' => $this->title,
            'bio' => $this->bio,
            'tag_id' => $tagId,
            'tag_name' => $this->tag ? $this->tag->name : null,
            'index' => $this->index,
            'visibility' => (bool) $this->visibility,
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'selling_price' => $product->selling_price,
                    'discount_price' => $product->discount_price,
                    'images' => ProductImage::where('product_id', $product->id)->get(),
                ];
            }),
        ];
    }
}
