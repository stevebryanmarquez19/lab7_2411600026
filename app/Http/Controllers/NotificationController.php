<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = $user ? $user->notifications()->latest()->paginate(15) : collect();
        return view('notifications.index', compact('notifications'));
    }

    public function markRead($notification)
    {
        $user = auth()->user();
        abort_unless($user, 403);
        $item = $user->notifications()->where('id', $notification)->firstOrFail();
        $item->markAsRead();
        return back()->with('success', 'Notification marked as read.');
    }
}
