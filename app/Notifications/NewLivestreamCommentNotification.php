<?php

namespace App\Notifications;

use App\Models\LivestreamComment;
use App\Support\Notification\Channels\FcmTopicChannel;
use App\Support\Notification\Contracts\FcmNotifiable;
use App\Support\Notification\Contracts\SupportsFcmTopicChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;

class NewLivestreamCommentNotification extends Notification implements ShouldQueue, SupportsFcmTopicChannel
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public LivestreamComment $comment)
    {
        //
    }

    public function toFcmTopic(FcmNotifiable|AnonymousNotifiable $notifiable): string
    {
        return $this->comment->livestream->getRoomName();
        // return 'livestream_comment_'.$this->comment->livestream_id;
    }

    public function toFcm(FcmNotifiable|AnonymousNotifiable $notifiable): CloudMessage
    {
        $user = $this->comment->user;

        return CloudMessage::new()
            ->withData([
                'user' => [
                    'id' => $user->getKey(),
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    // 'avatar' => $user->avatar_image, TODO: implement this feature
                ],
                'comment' => [
                    'id' => $this->comment->getKey(),
                    'title' => $this->comment->comment,
                ],
            ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [FcmTopicChannel::class];
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
