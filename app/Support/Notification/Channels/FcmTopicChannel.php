<?php

namespace App\Support\Notification\Channels;

use Illuminate\Support\Arr;
use Kreait\Firebase\Messaging\SendReport;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Events\Dispatcher;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Illuminate\Notifications\Events\NotificationFailed;
use App\Support\Notification\Contracts\SupportsFcmTopicChannel;
use App\Support\Notification\Concerns\CanProcessFcmNotification;

class FcmTopicChannel
{
    use CanProcessFcmNotification;

    /**
     * Create a new channel instance.
     */
    public function __construct(protected Dispatcher $events)
    {
        //
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification&SupportsFcmTopicChannel $notification): ?array
    {
        $topics = collect(Arr::wrap($notification->toFcmTopics($notifiable)))->filter();

        if ($topics->isEmpty()) {
            return null;
        }

        $fcmMessage = $notification->toFcm($notifiable);
        $fcmMessage = $this->addEventToFcmMessage($fcmMessage, $notification);

        try {
            $resultCollection = $topics
                ->chunk(100)
                ->map(fn ($chunkOfTopics) => $chunkOfTopics->map(fn ($topic) => $fcmMessage->toTopic($topic)))
                ->map(fn ($messages) => Firebase::messaging()->sendAll($messages->all()));

            $resultCollection->each(fn (MulticastSendReport $report) => $this->checkReportForFailures($notifiable, $notification, $report));

            return $resultCollection->flatten()->all();
        } catch (MessagingException $e) {
            $this->dispatchFailedNotificationForMessagingException($notifiable, $notification, $e);
        }

        return [];
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

    /**
     * Dispatch failed event.
     */
    protected function dispatchFailedNotificationForMessagingException(mixed $notifiable, Notification $notification, MessagingException $e): void
    {
        $this->events->dispatch(new NotificationFailed($notifiable, $notification, self::class, [
            'errors' => $e,
        ]));
    }
}
