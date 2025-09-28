<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('seller-status-{seller}', function (User $user, User $seller) {
    return $user->is($seller);
});
