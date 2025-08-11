<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SectionItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'image' => $this->image ? asset($this->image) : null,
            'title' => $this->title,
            'bio' => $this->bio,
            'tag_id' => $this->tag ? $this->tag->id : null,
            'tag_name' => $this->tag ? $this->tag->name : null,
            'index' => $this->index,
            'visibility' => (bool) $this->visibility,
        ];
    }
}
