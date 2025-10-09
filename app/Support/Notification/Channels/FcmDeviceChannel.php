<?php

namespace App\Support\Notification\Channels;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Kreait\Firebase\Messaging\SendReport;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Events\Dispatcher;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Illuminate\Notifications\Events\NotificationFailed;
use App\Support\Notification\Concerns\CanProcessFcmNotification;
use App\Support\Notification\Contracts\SupportsFcmDeviceChannel;

class FcmDeviceChannel
{
    use CanProcessFcmNotification;

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

    public function send($notifiable, Notification&SupportsFcmDeviceChannel $notification): ?Collection
    {
        $tokens = collect(Arr::wrap($notification->toFcmTokens($notifiable)))->filter();

        if ($tokens->isEmpty()) {
            return null;
        }

        $fcmMessage = $notification->toFcm($notifiable);
        $fcmMessage = $this->addEventToFcmMessage($fcmMessage, $notification);

        return $tokens
            ->chunk(self::TOKENS_PER_REQUEST)
            ->map(fn ($tokens) => Firebase::messaging()->sendMulticast($fcmMessage, $tokens->all()))
            ->map(fn (MulticastSendReport $report) => $this->checkReportForFailures($notifiable, $notification, $report));
    }

    /**
     * Handle the report for the notification and dispatch any failed notifications.
     */
    protected function checkReportForFailures(mixed $notifiable, Notification $notification, MulticastSendReport $report): MulticastSendReport
    {
        collect($report->getItems())
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
