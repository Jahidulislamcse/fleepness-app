<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\SizeTemplateItem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SizeTemplateItem
 */
class SizeTemplateItemResource extends JsonResource
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
            'size_name' => $this->size_name,
            'size_value' => $this->size_value,
            'template' => $this->whenLoaded('template', fn () => SizeTemplateResource::make($this->template)),
        ];
    }
}
