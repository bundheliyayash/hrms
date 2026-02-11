<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Client;
use App\Models\ClientSite;
use App\Models\EmployeeDetail;
use App\Models\Attendance;
use Carbon\Carbon;

class EnterpriseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        
        // 1. Truncate Tables
        Attendance::truncate();
        EmployeeDetail::truncate();
        User::truncate();
        ClientSite::truncate();
        Client::truncate();
        // DB::table('activity_logs')->truncate(); // If exists
        
        Schema::enableForeignKeyConstraints();

        // 2. Create Roles
        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@hrms.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        // Admin
        User::create([
            'name' => 'HR Manager',
            'email' => 'admin@hrms.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Manager
        User::create([
            'name' => 'Project Manager',
            'email' => 'manager@hrms.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        // Client
        $clientUser = User::create([
            'name' => 'Client User',
            'email' => 'client@techcorp.com',
            'password' => bcrypt('password'),
            'role' => 'client',
        ]);

        // 3. Create Client & Site
        $client = Client::create([
            'name' => 'TechCorp',
            'email' => 'contact@techcorp.com',
            'phone' => '1234567890',
            'address' => 'Silicon Valley'
        ]);

        $site = ClientSite::create([
            'client_id' => $client->id,
            'site_name' => 'Tech Park HQ',
            'address' => 'Building 4',
            'latitude' => '12.9716',
            'longitude' => '77.5946',
            'radius_meters' => 500
        ]);

        // 4. Create 5 Realistic Employees
        $employees = [
            ['name' => 'Amit Sharma', 'role' => 'Software Engineer', 'dept' => 'IT'],
            ['name' => 'Priya Verma', 'role' => 'HR Executive', 'dept' => 'HR'],
            ['name' => 'Rahul Singh', 'role' => 'Field Officer', 'dept' => 'Ops'],
            ['name' => 'Sneha Gupta', 'role' => 'Accountant', 'dept' => 'Finance'],
            ['name' => 'Vikram Malhotra', 'role' => 'Sales Manager', 'dept' => 'Sales'],
        ];

        foreach ($employees as $index => $emp) {
            $user = User::create([
                'name' => $emp['name'],
                'email' => strtolower(explode(' ', $emp['name'])[0]) . '@hrms.com',
                'password' => bcrypt('password'),
                'role' => 'employee'
            ]);

            EmployeeDetail::create([
                'user_id' => $user->id,
                'employee_id' => 'EMP' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'phone' => '987654321' . $index,
                'department' => $emp['dept'],
                'designation' => $emp['role'],
                'basic_salary' => rand(40000, 80000),
                'joining_date' => now()->subMonths(rand(2, 24)),
                'site_id' => $site->id
            ]);

            // Create Attendance (random)
            Attendance::create([
                'user_id' => $user->id,
                'date' => Carbon::today()->format('Y-m-d'),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'break_start' => '13:00:00',
                'break_end' => '14:00:00',
                'total_break_minutes' => 60,
                'status' => 'present',
                'is_verified' => true,
                'site_id' => $site->id
            ]);
        }
    }
}
