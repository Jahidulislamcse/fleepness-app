<?php

namespace App\Http\Resources;

use App\Models\UserPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserPayment
 */
class UserPaymentResource extends JsonResource
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
            'account_number' => $this->account_number,
            'user' => $this->whenLoaded('user', fn () => UserResource::make($this->user)),
            'payment_method' => $this->whenLoaded('paymentMethod', fn () => PaymentMethodResource::make($this->paymentMethod)),
            'created_at' => $this->created_at,
        ];
    }
}
