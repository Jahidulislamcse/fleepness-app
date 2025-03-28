<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        $userId = auth()->id();

        $notifications = Notification::where('user_id', $userId)
            ->where('status', 'unread')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Unread notifications retrieved successfully.',
            'notifications' => $notifications
        ], 200);
    }

    public function markAsRead()
    {
        $userId = auth()->id();
        Notification::where('user_id', $userId)->update(['status' => 'read']);
        return response()->json(['message' => 'Notifications marked as read']);
    }
}
