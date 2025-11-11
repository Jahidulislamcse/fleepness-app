<?php

use App\Models\User;
use App\Models\Livestream;
use App\Constants\LivestreamStatuses;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user_{id}', function (User $user, $id) { // selller or buyer will get their personal notifications here
    return (int) $user->getKey() === (int) $id;
}, ['guards' => ['sanctum']]);

Broadcast::channel('livestream_{livestream}', function (?User $user, Livestream $livestream) { // can join the livestream notifications only when livestream is started
    return $livestream->status === LivestreamStatuses::STARTED->value;
}, ['guards' => ['sanctum']]);
