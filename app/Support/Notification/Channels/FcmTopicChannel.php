<?php

namespace App\Support\Notification\Channels;

use App\Support\Notification\Contracts\FcmNotifiable;
use App\Support\Notification\Contracts\SupportsFcmTopicChannel;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FcmTopicChannel
{
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
    public function send(FcmNotifiable|AnonymousNotifiable $notifiable, Notification&SupportsFcmTopicChannel $notification): ?array
    {
        $topic = $notification->toFcmTopic($notifiable);

        if (empty($topic)) {
            return null;
        }

        $fcmMessage = $notification->toFcm($notifiable)->toTopic($topic);

        try {
            return Firebase::messaging()->send($fcmMessage);
        } catch (MessagingException $e) {
            $this->dispatchFailedNotification($notifiable, $notification, $e);
        }

        return [];
    }

    /**
     * Dispatch failed event.
     */
    protected function dispatchFailedNotification(mixed $notifiable, Notification $notification, MessagingException $e): void
    {
        $this->events->dispatch(new NotificationFailed($notifiable, $notification, self::class, [
            'errors' => $e,
        ]));
    }
}
