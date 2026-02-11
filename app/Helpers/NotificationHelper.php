<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;

class NotificationHelper
{
    /**
     * Send a system notification to a user.
     * 
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param string $type (info, success, warning, danger)
     * @param string|null $category (attendance, leaves, payroll)
     * @return Notification|null
     */
    public static function send($userId, $title, $message, $type = 'info', $category = null)
    {
        // Check if this category of notification is enabled in settings
        if ($category) {
            $enabled = Setting::where('key', 'notify_' . $category)->first();
            if ($enabled && $enabled->value == '0') {
                return null;
            }
        }

        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false
        ]);
    }

    /**
     * Send notification to all admins.
     */
    public static function notifyAdmins($title, $message, $type = 'info', $category = null)
    {
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            self::send($admin->id, $title, $message, $type, $category);
        }
    }
}
