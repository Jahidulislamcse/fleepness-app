<?php

use App\Models\User;
use App\Models\Livestream;
use App\Constants\LivestreamStatuses;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('seller-status-{seller}', function (User $user, User $seller) {
    return $user->is($seller);
}, ['guards' => ['sanctum']]);

Broadcast::channel('livestream_{livestream}', function (User $user, Livestream $livestream) {
    return $livestream->status !== LivestreamStatuses::FINISHED->value;
}, ['guards' => ['sanctum']]);
