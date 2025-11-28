<?php

use App\Models\User;
use App\Models\Livestream;
use App\Constants\LivestreamStatuses;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user_{id}', function (User $user, $id) { // selller or buyer will get their personal notifications here
    return (int) $user->getKey() === (int) $id;
}, ['guards' => ['sanctum']]);

// ! MUST COME BEFORE livestream_{livestream}
Broadcast::channel('livestream_feed', function () {
    return true;
});

Broadcast::channel('livestream_{livestream}', function (?User $user, Livestream $livestream) { // can join the livestream notifications only when livestream is started
    return LivestreamStatuses::STARTED === $livestream->status;
}, ['guards' => ['sanctum']]);
