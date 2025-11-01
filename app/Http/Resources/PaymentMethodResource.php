<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PaymentMethod
 */
class PaymentMethodResource extends JsonResource
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
            'name' => $this->name,
            'icon' => $this->icon,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
