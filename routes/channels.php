<?php

use App\Models\User;
use App\Models\Livestream;
use App\Constants\LivestreamStatuses;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user_{id}', function (User $user, $id) { // selller or buyer will get their personal notifications here
    return (int) $user->getKey() === (int) $id;
}, ['guards' => ['sanctum']]);

Broadcast::channel('livestream_{livestream}', function (?User $user, Livestream $livestream) { // can join the livestream notifications only when livestream is started
    return LivestreamStatuses::STARTED === $livestream->status;
}, ['guards' => ['sanctum']]);

Broadcast::channel('livestream_feed', function (?User $user) { // can join the livestream notifications only when livestream is started
    return true;
}, ['guards' => ['sanctum']]);
