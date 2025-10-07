<?php

use App\Constants\LivestreamStatuses;
use App\Models\Livestream;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('seller-status-{seller}', function (User $user, User $seller) {
    return $user->is($seller);
});

Broadcast::channel('livestream_{roomName}_{livestream}', function (User $user, string $roomName, Livestream $livestream) {
    // return true;
    return $livestream->status != LivestreamStatuses::FINISHED->value;
});
