<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('seller-status-{sellerId}', function ($user, $sellerId) {
    return (int) $user->id === (int) $sellerId;
});

