<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'section_name' => $this->section_name,
            'section_type' => $this->section_type,
            'section_title' => $this->section_title,
            'category_name' => $this->category ? $this->category->name : null,
            'index' => $this->index,
            'visibility' => (bool) $this->visibility,
            'background_image' => $this->background_image ? asset($this->background_image) : null,
            'banner_image' => $this->banner_image ? asset($this->banner_image) : null,
            'items' => SectionItemResource::collection($this->items),
        ];
    }
}
