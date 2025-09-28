<?php

namespace App\Support\Notification\Concerns;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;

trait CanProcessFcmNotification
{
    /**
     * Get the event name of the notification being broadcast.
     */
    public function broadcastAs(object $notification): string
    {
        return method_exists($notification, 'broadcastAs')
            ? $notification->broadcastAs()
            : $notification::class;
    }

    public function addEventToFcmMessage(CloudMessage $fcmMessage, Notification $notification): CloudMessage
    {
        $messageData = data_get($fcmMessage->jsonSerialize(), 'data', []);

        data_set($messageData, 'event', $this->broadcastAs($notification));
        $fcmMessage = $fcmMessage->withData($messageData);

        return $fcmMessage;
    }
}
