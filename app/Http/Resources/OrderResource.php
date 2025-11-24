<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'is_multi_seller' => $this->is_multi_seller,
            'total_sellers' => $this->total_sellers,
            'delivery_fee' => $this->delivery_fee,
            'product_cost' => $this->product_cost,
            'commission' => $this->commission,
            'platform_fee' => $this->platform_fee,
            'vat' => $this->vat,
            'grand_total' => $this->grand_total,
            'seller_orders' => $this->sellerOrders->map(function ($sellerOrder) {
                return [
                    'id' => $sellerOrder->id,
                    'seller_order_code' => $sellerOrder->seller_order_code,
                    'status' => $sellerOrder->status,
                    'product_cost' => $sellerOrder->product_cost,
                    'commission' => $sellerOrder->commission,
                    'vat' => $sellerOrder->vat,
                    'delivery_fee' => $sellerOrder->delivery_fee,
                    'items' => OrderItemResource::collection($sellerOrder->items),
                    'seller' => [
                        'id' => $sellerOrder->seller->id,
                        'name' => $sellerOrder->seller->name,
                    ],
                ];
            }),
            'customer' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone_number' => $this->user->phone_number,
                'email' => $this->user->email,
                'default_address' => $this->user->defaultAddress,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
