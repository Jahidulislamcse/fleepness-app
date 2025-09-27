<?php

namespace App\Support\Notification\Channels;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Kreait\Firebase\Messaging\SendReport;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Events\Dispatcher;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\MulticastSendReport;
use App\Support\Notification\Contracts\FcmNotifiable;
use Illuminate\Notifications\Events\NotificationFailed;
use App\Support\Notification\Contracts\SupportsFcmChannel;

class FcmDeviceChannel
{
    /**
     * The maximum number of tokens we can use in a single request
     *
     * @var int
     */
    const TOKENS_PER_REQUEST = 500;

    /**
     * Create a new channel instance.
     */
    public function __construct(protected Dispatcher $events) {}

    public function send(FcmNotifiable $notifiable, Notification&SupportsFcmChannel $notification): ?Collection
    {
        $tokens = Arr::wrap($notifiable->routeNotificationForFcmTokens($notification));

        if (empty($tokens)) {
            return null;
        }

        $fcmMessage = $notification->toFcm($notifiable);

        return Collection::make($tokens)
            ->chunk(self::TOKENS_PER_REQUEST)
            ->map(fn ($tokens) => Firebase::messaging()->sendMulticast($fcmMessage, $tokens->all()))
            ->map(fn (MulticastSendReport $report) => $this->checkReportForFailures($notifiable, $notification, $report));
    }

    /**
     * Handle the report for the notification and dispatch any failed notifications.
     */
    protected function checkReportForFailures(mixed $notifiable, Notification $notification, MulticastSendReport $report): MulticastSendReport
    {
        Collection::make($report->getItems())
            ->filter(fn (SendReport $report) => $report->isFailure())
            ->each(fn (SendReport $report) => $this->dispatchFailedNotification($notifiable, $notification, $report));

        return $report;
    }

    /**
     * Dispatch failed event.
     */
    protected function dispatchFailedNotification(mixed $notifiable, Notification $notification, SendReport $report): void
    {
        $this->events->dispatch(new NotificationFailed($notifiable, $notification, self::class, [
            'report' => $report,
        ]));
    }
}
