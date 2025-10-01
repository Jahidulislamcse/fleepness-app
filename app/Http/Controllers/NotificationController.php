<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request, #[CurrentUser] User $user)
    {
        $perPage = $request->integer('per_page', 15);

        $notifications = $user
            ->when('read' === $request->get('type'))
            ->readNotifications()
            ->when('unread' === $request->get('type'))
            ->unreadNotifications()
            ->latest()
            ->paginate(perPage: $perPage);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'message' => 'Unread notifications retrieved successfully.',
        ]);
    }

    public function markAsRead(DatabaseNotification $notification, #[CurrentUser] User $user)
    {
        abort_if($notification->notifiable()->isNot($user), Response::HTTP_NOT_FOUND);

        $notification->markAsRead();

        return response()->json(['message' => 'Notifications marked as read']);
    }

    public function markAllAsRead(#[CurrentUser] User $user)
    {
        return DB::transaction(function () use ($user) {
            $user->notifications->markAsRead();

            return response()->json(['message' => 'Notifications marked as read']);
        });
    }
}
