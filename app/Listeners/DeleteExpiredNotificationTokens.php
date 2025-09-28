<?php

namespace App\Listeners;

use Illuminate\Support\Arr;
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
    public function handle(NotificationFailed $event): void
    {
        if (FcmDeviceChannel::class === $event->channel) {
            /** @var SendReport */
            $report = Arr::get($event->data, 'report');

            $target = $report->target();

            $notifiable = $event->notifiable;

            if ($notifiable instanceof FcmNotifiableByDevice) {
                $notifiable->removeDeviceToken($target->value());
            }
        }
    }
}
