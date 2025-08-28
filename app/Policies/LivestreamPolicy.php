<?php

namespace App\Policies;

use App\Constants\LivestreamStatuses;
use App\Models\Livestream;
use App\Models\User;
// use App\Models\Vendor;
use Illuminate\Auth\Access\Response;

class LivestreamPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response|bool
    {
        // Ensure that the user has the 'vendor' role
        // if ($user->role !== 'vendor') {
        // return Response::denyAsNotFound(); // Deny if the user is not a vendor
        // }

        return true; // Proceed if the user is a vendor
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Livestream $livestream): Response|bool
    {
        // $userVendor = $user->vendors->first();

        $canSee = $livestream->vendor()->is($user) && $livestream->status !== LivestreamStatuses::FINISHED->value && is_null($livestream->ended_at);

        if (! $canSee) {
            return Response::denyAsNotFound();
        }

        return true;
    }

    public function getPublisherToken(User $user, Livestream $livestream): Response|bool
    {
        return $this->update($user, $livestream);
    }

    public function getSubscriberToken(?User $user, Livestream $livestream): Response|bool
    {
        $canSee = $livestream->status !== LivestreamStatuses::FINISHED->value && is_null($livestream->ended_at);

        if (! $canSee) {
            return Response::denyAsNotFound();
        }

        return true;
    }

    public function addProducts(User $user, Livestream $livestream): Response|bool
    {
        return $this->update($user, $livestream);
    }

    public function removeProducts(User $user, Livestream $livestream): Response|bool
    {
        // return $this->update($user, $livestream);
        return true;

    }
}
