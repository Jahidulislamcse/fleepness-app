<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Auth\Access\Response;

class VendorPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $user->loadCount('vendors');

        return $user->vendors_count === 0;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vendor $vendor): bool|Response
    {
        if ($vendor->user->isNot($user)) {
            return Response::denyAsNotFound();
        }

        return true;
    }

    public function follow(User $user, Vendor $vendor): bool|Response
    {
        return $vendor->user->isNot($user) && $vendor->followers()
            ->where('id', $user->getKey())->doesntExist();
    }

    public function unfollow(User $user, Vendor $vendor): bool|Response
    {
        return $vendor->user->isNot($user) && $vendor->followers()
            ->where('id', $user->getKey())->exists();
    }
}
