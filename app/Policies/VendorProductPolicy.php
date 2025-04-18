<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Auth\Access\Response;

class VendorProductPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Vendor $vendor): bool
    {
        return $vendor->user->is($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vendor $vendor, Product $product): bool|Response
    {

        return $vendor->user->is($user) && $product->vendor->is($vendor) ? true : Response::denyAsNotFound();
    }
}
