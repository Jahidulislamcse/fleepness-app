<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Livestream;
use Illuminate\Auth\Access\Response;
// use App\Models\Vendor;
use App\Constants\LivestreamStatuses;

class LivestreamPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return 'vendor' === $user->role;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Livestream $livestream): bool|Response
    {
        $canSee = 'vendor' === $user->role && $livestream->status !== LivestreamStatuses::FINISHED->value && is_null($livestream->ended_at);

        if (! $canSee) {
            return Response::denyAsNotFound();
        }

        return true;
    }

    public function getPublisherToken(User $user, Livestream $livestream): bool|Response
    {
        return $this->update($user, $livestream);
    }

    public function getSubscriberToken(?User $user, Livestream $livestream): bool|Response
    {
        if(!$user){
            return true;
        }
        $canSee = 'user' === $user->role && $livestream->status !== LivestreamStatuses::FINISHED->value && is_null($livestream->ended_at);

        if (! $canSee) {
            return Response::denyAsNotFound();
        }

        return true;
    }

    public function addProducts(User $user, Livestream $livestream): bool|Response
    {
        return $this->update($user, $livestream);
    }

    public function removeProducts(User $user, Livestream $livestream): bool|Response
    {
        // return $this->update($user, $livestream);
        return true;
    }
}
