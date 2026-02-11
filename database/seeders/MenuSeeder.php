<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\MenuPermission;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Clear old menus safely
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Menu::truncate();
        MenuPermission::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Define Menus
        $menus = [
            // [Title, Route, Icon, Order, Active, Roles[]]
            ['Dashboard', 'dashboard', 'bi-speedometer2', 1, true, ['admin']],
            
            // Admin Modules
            ['Manage Clients', 'admin.clients.index', 'bi-briefcase', 2, true, ['admin']],
            ['Manage Sites', 'admin.sites.index', 'bi-geo-alt', 3, true, ['admin']],
            ['Employees', 'admin.employees.index', 'bi-people', 4, true, ['admin']],
            ['Leave Requests', 'admin.leaves.index', 'bi-calendar-check', 5, true, ['admin']],
            ['Payroll', 'admin.payroll.index', 'bi-cash-stack', 6, true, ['admin']],
            ['Reports', 'admin.reports.index', 'bi-graph-up', 7, true, ['admin']],
            ['Attendance Logs', 'admin.attendance.index', 'bi-clock-history', 8, true, ['admin', 'manager']],
            ['Correction Req.', 'admin.attendance.requests', 'bi-exclamation-octagon', 9, true, ['admin', 'manager']],
            ['Documents', 'admin.documents.index', 'bi-file-earmark-lock', 10, true, ['admin']],
            ['Audit Logs', 'admin.activity-logs.index', 'bi-activity', 11, true, ['admin']],

            // Manager Modules
            ['Team Dashboard', 'manager.dashboard', 'bi-speedometer2', 1, true, ['manager']],

             // Employee Modules
            ['My Dashboard', 'employee.dashboard', 'bi-speedometer2', 1, true, ['employee']],
            ['My Attendance', 'employee.attendance.index', 'bi-clock', 2, true, ['employee']],
            ['My Leaves', 'employee.leaves.index', 'bi-calendar-plus', 3, true, ['employee']],
            ['My Payslips', 'employee.payroll.index', 'bi-receipt', 4, true, ['employee']],
        ];

        foreach ($menus as $m) {
            $menu = Menu::create([
                'title' => $m[0],
                'route_name' => $m[1],
                'icon' => $m[2],
                'order' => $m[3],
                'is_active' => $m[4]
            ]);

            foreach ($m[5] as $role) {
                MenuPermission::create([
                    'menu_id' => $menu->id,
                    'role' => $role
                ]);
            }
        }
    }
}
