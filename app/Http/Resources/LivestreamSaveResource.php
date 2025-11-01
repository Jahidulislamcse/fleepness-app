<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\LivestreamSave;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LivestreamSave
 */
class LivestreamSaveResource extends JsonResource
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
            'user' => $this->whenLoaded('user', fn () => UserResource::make($this->user)),
            'livestream' => $this->whenLoaded('livestream', fn () => LivestreamResource::make($this->livestream)),
        ];
    }
}
