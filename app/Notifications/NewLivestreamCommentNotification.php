<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\LivestreamComment;
use App\Constants\LivestreamStatuses;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewLivestreamCommentNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use InteractsWithSockets, Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public LivestreamComment $comment)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [
            'broadcast',
        ];
    }

    #[\Override]
    public function broadcastOn()
    {
        return [
            $this->comment->livestream->room_name,
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
                'avatar' => $commenter->cover_image,
                'phone_number' => $commenter->phone_number,
            ],
            'comment' => [
                'id' => $this->comment->getKey(),
                'title' => $this->comment->comment,
            ],
        ];
    }

    public function broadcastAs(): string
    {
        return 'new_livestream_comment';
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [

        ];
    }

    public function shouldSend(object $notifiable, string $channel): bool
    {
        $isFinished = LivestreamStatuses::FINISHED === $this->comment->livestream;
        return !($isFinished && 'broadcast' === $channel);
    }
}
