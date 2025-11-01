<?php

namespace App\Http\Resources;

use App\Models\SizeTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SizeTemplate
 */
class SizeTemplateResource extends JsonResource
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
            'template_name' => $this->template_name,
            'items' => $this->whenLoaded('items', fn () => SizeTemplateItemResource::collection($this->items)),
        ];
    }
}
