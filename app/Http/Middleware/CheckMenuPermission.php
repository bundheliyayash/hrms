<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;
use App\Models\MenuPermission;

class CheckMenuPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()->getName();
        $user = Auth::user();

        if ($user && $routeName) {
            // 0. Absolute Bypass for Admin
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return $next($request);
            }

            // 1. Check for Exact Route Match First
            $menu = Menu::where('route_name', $routeName)->first();

            // 2. If no exact match, try to find a parent menu
            if (!$menu) {
                $parts = explode('.', $routeName);
                if (count($parts) >= 2) {
                    $parentRoute = $parts[0] . '.' . $parts[1] . '.index';
                    $menu = Menu::where('route_name', $parentRoute)->first();
                }
            }

            if ($menu) {
                // Check if user's role has permission for this menu (Legacy Check)
                $hasLegacyPermission = MenuPermission::where('menu_id', $menu->id)
                    ->where('role', $user->role)
                    ->exists();

                // Check for dynamic permission (New RBAC)
                $permissionMap = [
                    'attendance.requests' => 'manage_attendance_requests',
                    'attendance' => 'view_employees', // Fallback to view_employees for general attendance
                    'employees' => 'view_employees',
                    'shifts' => 'manage_shifts',
                    'assignments' => 'manage_assignments',
                    'clients' => 'manage_clients',
                    'sites' => 'manage_clients',
                    'payroll' => 'view_payroll',
                    'reports' => 'view_reports',
                    'roles' => 'manage_rbac',
                    'settings' => 'manage_settings',
                    'activity-logs' => 'view_activity_logs',
                    'holidays' => 'manage_employees',
                ];

                $permissionName = null;
                foreach ($permissionMap as $segment => $permission) {
                    if (str_contains($routeName, $segment)) {
                        $permissionName = $permission;
                        break;
                    }
                }

                $hasRbacPermission = $permissionName ? $user->hasPermission($permissionName) : false;

                if (!$hasLegacyPermission && !$hasRbacPermission) {
                    // Check if it's an active menu item first
                    if ($menu->is_active === false) {
                        abort(403, 'This module is currently disabled by Admin.');
                    }
                    abort(403, 'Access to this module is disabled by Admin.');
                }
            }
        }

        return $next($request);
    }
}
