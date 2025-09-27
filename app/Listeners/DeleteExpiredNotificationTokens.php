<?php

namespace App\Listeners;

use App\Models\User;
use App\Support\Notification\Channels\FcmDeviceChannel;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Support\Arr;
use Kreait\Firebase\Messaging\SendReport;

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
        if ($event->channel === FcmDeviceChannel::class) {
            /** @var SendReport */
            $report = Arr::get($event->data, 'report');

            $target = $report->target();

            /** @var User */
            $user = $event->notifiable;

            $user
                ->deviceTokens()
                ->where('token', $target->value())
                ->delete();
        }
    }
}
