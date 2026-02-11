<?php

namespace App\Http\View\Composers;

use App\Models\Menu;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class SidebarViewComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (Auth::check()) {
            $role = Auth::user()->role;
            
            $menus = Menu::where('is_active', true)
                ->whereHas('permissions', function ($query) use ($role) {
                    $query->where('role', $role);
                })
                ->orderBy('order')
                ->get();

            $notifications = \App\Models\Notification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->latest()
                ->take(5)
                ->get();

            $unreadCount = \App\Models\Notification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->count();

            $systemSettings = [
                'app_name' => \App\Models\Setting::where('key', 'app_name')->first()?->value ?? config('app.name'),
                'company_name' => \App\Models\Setting::where('key', 'company_name')->first()?->value ?? 'CleanHR',
                'footer_text' => \App\Models\Setting::where('key', 'footer_text')->first()?->value ?? '© ' . date('Y') . ' CleanHR'
            ];

            $view->with([
                'sideMenus' => $menus,
                'userNotifications' => $notifications,
                'unreadNotificationsCount' => $unreadCount,
                'systemSettings' => $systemSettings
            ]);
        }
    }
}
