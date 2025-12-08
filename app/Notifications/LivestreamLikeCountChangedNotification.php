<?php

namespace App\Notifications;

use App\Models\Livestream;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class LivestreamLikeCountChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly Livestream $livestream)
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
        return ['broadcast'];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'likes_count' => $this->livestream->likes_count,
        ];
    }

    public function broadcastAs(): string
    {
        return 'livestream_like_count_changed';
    }

    #[\Override]
    public function broadcastOn()
    {
        return [
            $this->livestream->room_name,
        ];
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
