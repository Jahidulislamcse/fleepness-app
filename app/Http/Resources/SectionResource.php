<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    public function toArray($request)
    {
        $allowedSectionTypes = ['scrollable_product', 'spotlight_deals', 'lighting_deals', 'search'];

        $showProducts = in_array($this->section_type, $allowedSectionTypes) ? 1 : 0;

        $response = [
            'id' => $this->id,
            'section_name' => $this->section_name,
            'section_type' => $this->section_type,
            'section_title' => $this->section_title,
            'bio' => $this->bio,
            'category_name' => $this->category ? $this->category->name : null,
            'tag_id' => (in_array($this->section_type, $allowedSectionTypes) && $this->items->isNotEmpty())
                ? $this->items->first()->tag->id
                : null,
            'tag_name' => (in_array($this->section_type, $allowedSectionTypes) && $this->items->isNotEmpty())
                ? $this->items->first()->tag->name
                : null,
            'placement_type' => $this->placement_type,
            'index' => $this->index,
            'cat_index' => $this->cat_index,
            'visibility' => (bool) $this->visibility,
            'background_image' => $this->background_image ? asset($this->background_image) : null,
            'banner_image' => $this->banner_image ? asset($this->banner_image) : null,
            'show_products' => $showProducts,
            'items' => SectionItemResource::collection($this->items->map(function($item) use ($showProducts) {
                $item->show_products = $showProducts;
                return $item;
            })),
        ];

        return $response;
    }
}
