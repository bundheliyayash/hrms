<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\EmployeeDetail;
use App\Models\Shift;
use App\Models\ClientSite;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = Shift::all();
        $sites = ClientSite::all();
        $managerRole = Role::where('name', 'manager')->first();
        $employeeRole = Role::where('name', 'employee')->first();

        // 1. Create Managers
        $managers = [
            'Rajesh Kumar', 'Amit Singh', 'Suresh Iyer', 'Vikram Deshmukh', 'Priya Sharma'
        ];

        $managerUsers = [];
        foreach ($managers as $name) {
            $email = strtolower(str_replace(' ', '.', $name)) . '@demo.com';
            $user = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => 'manager',
                'role_id' => $managerRole->id ?? null,
                'status' => 'active',
            ]);
            $managerUsers[] = $user;

            EmployeeDetail::updateOrCreate(['user_id' => $user->id], [
                'employee_id' => 'MGR' . rand(1000, 9999),
                'phone' => '+91 9' . rand(100000000, 999999999),
                'department' => 'Operations',
                'designation' => 'Operations Manager',
                'joining_date' => Carbon::now()->subYears(3),
                'basic_salary' => rand(45000, 75000),
                'shift_id' => rand(1, 4),
            ]);
        }

        // 2. Create Field Staff (Employees)
        $firstNames = ['Rahul', 'Anil', 'Sunil', 'Vijay', 'Deepak', 'Sanjay', 'Arun', 'Kiran', 'Pooja', 'Sneha', 'Meena', 'Ramesh', 'Ganesh', 'Harish', 'Pradeep', 'Manoj'];
        $lastNames = ['Patil', 'Sharma', 'Verma', 'Gupta', 'Reddy', 'Nair', 'More', 'Desai', 'Joshi', 'Mishra'];

        for ($i = 1; $i <= 50; $i++) {
            $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)] . ' ' . $i;
            $email = 'emp' . $i . '@demo.com';
            
            $user = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => 'employee',
                'role_id' => $employeeRole->id ?? null,
                'status' => rand(1, 10) > 1 ? 'active' : 'inactive',
            ]);

            $randomManager = $managerUsers[array_rand($managerUsers)];
            $randomSite = $sites->random();
            $randomShift = $shifts->random();

            EmployeeDetail::updateOrCreate(['user_id' => $user->id], [
                'employee_id' => 'EMP' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'phone' => '+91 8' . rand(100000000, 999999999),
                'department' => array_rand(['Housekeeping' => 0, 'Maintenance' => 1, 'Security' => 2, 'Ops' => 3]),
                'designation' => 'Field Executive',
                'joining_date' => Carbon::now()->subMonths(rand(1, 36)),
                'basic_salary' => rand(18000, 28000),
                'manager_id' => $randomManager->id,
                'site_id' => $randomSite->id,
                'shift_id' => $randomShift->id,
                'employment_type' => 'On-roll',
                'bank_name' => 'State Bank of India',
                'account_number' => '3000' . rand(10000000, 99999999),
                'ifsc_code' => 'SBIN0001234',
                'aadhar_number' => rand(1000, 9999) . ' ' . rand(1000, 9999) . ' ' . rand(1000, 9999),
                'pan_number' => strtoupper(substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5)) . rand(1000, 9999) . 'Z',
            ]);
        }
    }
}
