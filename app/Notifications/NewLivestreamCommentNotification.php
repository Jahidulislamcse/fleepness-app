<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\LivestreamComment;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
// use App\Support\Notification\Channels\FcmTopicChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Support\Notification\Contracts\FcmNotifiableByTopic;
use App\Support\Notification\Contracts\FcmNotifiableByDevice;
use App\Support\Notification\Contracts\SupportsFcmTopicChannel;

class NewLivestreamCommentNotification extends Notification implements ShouldBroadcast, ShouldQueue, SupportsFcmTopicChannel
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public LivestreamComment $comment)
    {
        //
    }

    public function toFcmTopic(AnonymousNotifiable|FcmNotifiableByTopic $notifiable): string
    {
        return $notifiable->routeNotificationForFcmTopics($this);
    }

    public function toFcm(AnonymousNotifiable|FcmNotifiableByDevice|FcmNotifiableByTopic $notifiable): CloudMessage
    {
        $commenter = $this->comment->user;

        return CloudMessage::new()
            ->withData([
                'commenter' => Json::encode([
                    'id' => $commenter->getKey(),
                    'name' => $commenter->name,
                    'email' => $commenter->email,
                    'phone_number' => $commenter->phone_number,
                    // 'avatar' => $user->avatar_image, TODO: implement this feature
                ]),
                'comment' => Json::encode([
                    'id' => $this->comment->getKey(),
                    'title' => $this->comment->comment,
                ]),
            ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            // FcmTopicChannel::class,
            'broadcast',
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        $commenter = $this->comment->user;

        return [
            'commenter' => [
                'id' => $commenter->getKey(),
                'name' => $commenter->name,
                'email' => $commenter->email,
                'phone_number' => $commenter->phone_number,
                // 'avatar' => $user->avatar_image, TODO: implement this feature
            ],
            'comment' => [
                'id' => $this->comment->getKey(),
                'title' => $this->comment->comment,
            ],
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [
            $this->comment->livestream->getRoomName(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'new_livestream_comment';
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
