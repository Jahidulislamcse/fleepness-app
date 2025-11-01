<?php

namespace App\Http\Resources;

use App\Models\Livestream;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Livestream $resource
 *
 * @mixin Livestream
 */
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
            $this->getKeyName() => $this->getKey(),
            'title' => $this->title,
            'room_name' => $this->room_name,
            'total_duration' => $this->total_duration,
            'scheduled_time' => $this->scheduled_time,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'total_participants' => $this->total_participants,
            'status' => $this->status,

            $this->mergeWhen(! is_null($this->egress_id), fn () => [
                'recordings' => $this->recordings,
                'short_videos' => $this->short_videos,
                'thumbnails' => $this->thumbnails,
            ]),

            'products' => $this->whenLoaded('products', fn () => ProductResource::collection($this->products)),
            'vendor' => $this->whenLoaded('vendor', fn () => UserResource::make($this->vendor)),
            'participants' => $this->whenLoaded('participants', fn () => UserResource::collection($this->participants)),
            'likes' => $this->whenLoaded('likes', fn () => LivestreamLikeResource::collection($this->likes)),
            'saves' => $this->whenLoaded('saves', fn () => LivestreamSaveResource::collection($this->saves)),
        ];
    }
}
