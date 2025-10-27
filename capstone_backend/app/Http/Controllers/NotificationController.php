<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Get user's notifications
    public function index(Request $request)
    {
        $user = $request->user();

        $query = $user->notifications()->latest();

        // Filter by type if provided
        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        // Filter by read status
        if ($request->query('unread') === 'true') {
            $query->unread();
        }

        return $query->paginate(20);
    }

    // Mark notification as read
    public function markAsRead(Request $request, Notification $notification)
    {
        // Ensure user owns the notification
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    // Mark all notifications as read
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        $user->notifications()->unread()->update([
            'read' => true,
            'read_at' => now()
        ]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    // Get unread notifications count
    public function unreadCount(Request $request)
    {
        $user = $request->user();

        $count = $user->unreadNotifications()->count();

        return response()->json(['count' => $count]);
    }

    // Delete notification
    public function destroy(Request $request, Notification $notification)
    {
        // Ensure user owns the notification
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->delete();

        return response()->json(['message' => 'Notification deleted']);
    }
}
