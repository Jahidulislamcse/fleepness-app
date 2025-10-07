<?php

namespace App\Support\Notification\Contracts;

use Illuminate\Notifications\Notification;

interface FcmNotifiableByDevice
{
    // /**
    //  * @return list<string>|string|null
    //  */
    // public function routeNotificationForFcmTokens(Notification&SupportsFcmChannel $notification): null|array|string;

    /**
     * @param  string|list<string>  $token
     */
    public function removeDeviceToken(array|string $token): mixed;
}
