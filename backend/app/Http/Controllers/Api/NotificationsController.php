<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationsController extends Controller
{
    /**
     * Display a listing of the notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        Log::info('Notifications for user ' . $user->id . ': ' . json_encode($notifications));

        return response()->json($notifications);
    }

    /**
     * Test method to get notifications for any user id.
     */
    public function getByUserId($userId)
    {
        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllRead(Request $request)
    {
        $user = Auth::user();

        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifications marked as read']);
    }

    /**
     * Delete all read notifications for the authenticated user.
     */
    public function deleteReadNotifications(Request $request)
    {
        $user = Auth::user();

        Notification::where('user_id', $user->id)
            ->whereNotNull('read_at')
            ->delete();

        return response()->json(['message' => 'Read notifications deleted']);
    }
}
