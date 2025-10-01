<?php

namespace App\Support\Notification\Contracts;

interface FcmBroadcastNotifiableByDevice
{
    /**
     * @return list<string>|string|null
     */
    public function routeBroadcastNotificationForFcmTokens(): null|array|string;

    /**
     * @param  string|list<string>  $token
     */
    public function removeDeviceToken(array|string $token): mixed;
}
