<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;

class OrderPolicy
{
    /**
     * Determine whether the user can permanently delete the model.
     */
    public function makePickupRequestToPathao(User $seller, Order $order): Response|bool
    {
        $vendor = $seller->vendors->first();

        /** @var OrderLine */
        $firstProductLine = $order->productLines->first();
        /** @var ProductVariant */
        $productVariant = $firstProductLine->purchasable;
        /** @var Product */
        $product = $productVariant->product;

        if ($product->vendor->isNot($vendor)) {
            return Response::denyAsNotFound();
        }

        if ($order->isDraft()) {
            return Response::deny('Order is not placed');
        }

        return true;
    }
}
