<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id === auth()->id()) {
            $notification->update(['is_read' => true]);
        }
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())->update(['is_read' => true]);
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
