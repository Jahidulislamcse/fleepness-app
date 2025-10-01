<?php

namespace App\Listeners;

use Illuminate\Support\Arr;
use App\Events\FcmBroadcastFailed;
use Kreait\Firebase\Messaging\SendReport;
use App\Support\Notification\Channels\FcmDeviceChannel;
use Illuminate\Notifications\Events\NotificationFailed;
use App\Support\Notification\Contracts\FcmNotifiableByDevice;

class DeleteExpiredNotificationTokens
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FcmBroadcastFailed|NotificationFailed $event): void
    {
        if ($event instanceof NotificationFailed && (FcmDeviceChannel::class === $event->channel || 'fcm-device' === $event->channel)) {
            /** @var SendReport */
            $report = Arr::get($event->data, 'report');

            $target = $report->target();

            $notifiable = $event->notifiable;

            if ($notifiable instanceof FcmNotifiableByDevice) {
                $notifiable->removeDeviceToken($target->value());
            }
        }

        if ($event instanceof FcmBroadcastFailed) {
            /** @var SendReport */
            $report = Arr::get($event->data, 'report');

            $target = $report->target();

            $notifiable = $event->notifiable;

            $notifiable->removeDeviceToken($target->value());
        }
    }
}
