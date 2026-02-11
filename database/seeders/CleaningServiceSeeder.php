<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\EmployeeDetail;
use App\Models\Client;
use App\Models\ClientSite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CleaningServiceSeeder extends Seeder
{
    public function run(): void
    {
        // SAFETY: This seeder uses idempotent logic (firstOrCreate) to prevent duplicates
        // It will NOT delete existing data - safe to run multiple times

        // 2. Create Clients & Sites (Realistic Mumbai/Bangalore Locations)
        $client1 = Client::firstOrCreate(
            ['email' => 'rahul@techpark.com'],
            ['name' => 'Tech Park Cleaning Services', 'contact_person' => 'Rahul Sharma']
        );
        $site1 = ClientSite::firstOrCreate(
            ['site_name' => 'Main Gate - Block A', 'client_id' => $client1->id],
            [
                'latitude' => 19.0760, // Mumbai
                'longitude' => 72.8777,
                'radius_meters' => 500
            ]
        );

        $client2 = Client::firstOrCreate(
            ['email' => 'amit@skyline.com'],
            ['name' => 'Skyline Residences', 'contact_person' => 'Amit Verma']
        );
        $site2 = ClientSite::firstOrCreate(
            ['site_name' => 'Clubhouse Area', 'client_id' => $client2->id],
            [
                'latitude' => 12.9716, // Bangalore
                'longitude' => 77.5946,
                'radius_meters' => 300
            ]
        );

        // 3. Create 1 Admin
        User::firstOrCreate(
            ['email' => 'admin@clean.com'],
            [
                'name' => 'Clean Admin',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ]
        );

        // 4. Create 1 Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@clean.com'],
            [
                'name' => 'Field Manager',
                'password' => Hash::make('password'),
                'role' => 'manager'
            ]
        );
        EmployeeDetail::firstOrCreate(
            ['user_id' => $manager->id],
            [
                'employee_id' => 'MGR001',
                'designation' => 'Operations Manager',
                'employment_type' => 'On-roll'
            ]
        );

        // 5. Create 3 Employees
        $employees = [
            ['name' => 'Arun Kumar', 'email' => 'emp1@clean.com', 'id' => 'EMP001', 'site' => $site1, 'type' => 'On-roll'],
            ['name' => 'Suresh Raina', 'email' => 'emp2@clean.com', 'id' => 'EMP002', 'site' => $site1, 'type' => 'Temporary'],
            ['name' => 'Deepak Singh', 'email' => 'emp3@clean.com', 'id' => 'EMP003', 'site' => $site2, 'type' => 'On-roll'],
        ];

        foreach ($employees as $emp) {
            $user = User::firstOrCreate(
                ['email' => $emp['email']],
                [
                    'name' => $emp['name'],
                    'password' => Hash::make('password'),
                    'role' => 'employee'
                ]
            );

            EmployeeDetail::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => $emp['id'],
                    'designation' => 'Cleaning Professional',
                    'site_id' => $emp['site']->id,
                    'manager_id' => $manager->id, // Assigned to our one manager
                    'employment_type' => $emp['type'],
                    'basic_salary' => 15000 // Indian Rupees
                ]
            );

            // Add 1 Sample Leave
            \App\Models\Leave::create([
                'user_id' => $user->id,
                'leave_type' => 'Sick Leave',
                'start_date' => now()->subDays(5)->format('Y-m-d'),
                'end_date' => now()->subDays(4)->format('Y-m-d'),
                'reason' => 'Fever',
                'status' => 'approved'
            ]);

            // Add 1 Sample Payroll (Last Month)
            \App\Models\Payroll::create([
                'user_id' => $user->id,
                'month' => 'December',
                'year' => 2025,
                'basic_salary' => 15000,
                'total_days' => 31,
                'present_days' => 25,
                'payable_days' => 26,
                'per_day_salary' => 483.87,
                'net_salary' => 12580.62,
                'status' => 'paid'
            ]);
        }
    }
}
