<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LivestreamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            ...parent::toArray($request),
            'status' => $this->status,
            'products'=> $this->products,
            $this->mergeWhen(!is_null($this->egress_id), fn()=> [
                'recordings' => $this->recordings,
                'short_videos' => $this->short_videos,
                'thumbnails' => $this->thumbnails,
            ])
        ];
    }
}
