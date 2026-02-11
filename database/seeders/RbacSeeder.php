<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Permissions
        $permissions = [
            // Employee Management
            ['name' => 'view_employees', 'display_name' => 'View Employees', 'category' => 'Employee Management'],
            ['name' => 'manage_employees', 'display_name' => 'Manage Employees', 'category' => 'Employee Management'],
            
            // Payroll
            ['name' => 'view_payroll', 'display_name' => 'View Payroll', 'category' => 'Payroll'],
            ['name' => 'manage_payroll', 'display_name' => 'Manage Payroll', 'category' => 'Payroll'],
            
            // Client/Site
            ['name' => 'manage_clients', 'display_name' => 'Manage Clients', 'category' => 'Site Management'],
            ['name' => 'manage_assignments', 'display_name' => 'Manage Daily Assignments', 'category' => 'Site Management'],
            
            // Attendance Corrections
            ['name' => 'manage_attendance_requests', 'display_name' => 'Manage Attendance Corrections', 'category' => 'Operations'],

            // Shifts
            ['name' => 'manage_shifts', 'display_name' => 'Manage Shifts & Timings', 'category' => 'Operations'],

            // Reports
            ['name' => 'view_reports', 'display_name' => 'View All Reports', 'category' => 'Reports'],
            
            // System
            ['name' => 'view_activity_logs', 'display_name' => 'View Activity Logs', 'category' => 'System'],
            ['name' => 'manage_settings', 'display_name' => 'Manage System Settings', 'category' => 'System'],
            ['name' => 'manage_rbac', 'display_name' => 'Manage Roles & Permissions', 'category' => 'System'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['name' => $p['name']], $p);
        }

        // 2. Create Roles
        $adminRole = Role::updateOrCreate(['name' => 'admin'], [
            'display_name' => 'Administrator',
            'description' => 'Full access to all system modules.'
        ]);

        $managerRole = Role::updateOrCreate(['name' => 'manager'], [
            'display_name' => 'Operations Manager',
            'description' => 'Manage staff attendance and on-site operations.'
        ]);

        $employeeRole = Role::updateOrCreate(['name' => 'employee'], [
            'display_name' => 'General Employee',
            'description' => 'Standard access to self-service portal.'
        ]);

        // 3. Assign Permissions to Admin (All)
        $adminRole->permissions()->sync(Permission::all());

        // 4. Assign Permissions to Manager (Grant almost all for "All Access" request)
        $managerPermissions = Permission::whereNotIn('name', ['manage_rbac'])->get();
        $managerRole->permissions()->sync($managerPermissions);

        // 5. Link Existing Users to Roles based on their string 'role' column
        $users = User::all();
        foreach ($users as $user) {
            $role = Role::where('name', $user->role)->first();
            if ($role) {
                $user->role_id = $role->id;
                $user->save();
            }
        }
    }
}
