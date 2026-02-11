<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\EmployeeDetail;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Payroll;
use Carbon\Carbon;

class FullSystemSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure Admin exists (handled by AdminUserSeeder usually, but we safeguard here)
        if (!User::where('email', 'admin@hrms.com')->exists()) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'admin@hrms.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
        }

        // 2. Create 10 Employees
        $departments = ['IT', 'HR', 'Sales', 'Marketing'];
        $designations = ['Developer', 'Manager', 'Executive', 'Intern'];

        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => "Employee $i",
                'email' => "employee$i@hrms.com",
                'password' => Hash::make('password'),
                'role' => 'employee',
            ]);

            EmployeeDetail::create([
                'user_id' => $user->id,
                'employee_id' => 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'phone' => '123456789' . $i,
                'address' => "Street $i, City",
                'department' => $departments[array_rand($departments)],
                'designation' => $designations[array_rand($designations)],
                'joining_date' => Carbon::now()->subMonths(rand(1, 24)),
                'basic_salary' => rand(3000, 8000),
            ]);

            // 3. Mark Attendance for last 30 days
            $startDate = Carbon::now()->subDays(30);
            for ($day = 0; $day < 30; $day++) {
                $currentDate = $startDate->copy()->addDays($day);
                
                // Skip weekends (approx)
                if ($currentDate->isWeekend()) continue;

                $status = 'present';
                $clockIn = '09:00:00';
                $clockOut = '18:00:00';

                // Randomize
                $rand = rand(1, 10);
                if ($rand == 1) {
                    $status = 'absent';
                    $clockIn = null;
                    $clockOut = null;
                } elseif ($rand == 2) {
                    $status = 'late';
                    $clockIn = '10:30:00';
                }

                if ($status !== 'absent') {
                    Attendance::create([
                        'user_id' => $user->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'status' => $status,
                    ]);
                } else {
                     Attendance::create([
                        'user_id' => $user->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'status' => 'absent',
                    ]);
                }
            }

            // 4. Create dummy Leave requests
            Leave::create([
                'user_id' => $user->id,
                'leave_type' => 'Sick Leave',
                'start_date' => Carbon::now()->subDays(rand(5, 10)),
                'end_date' => Carbon::now()->subDays(rand(3, 4)),
                'reason' => 'Feeling unwell',
                'status' => 'approved',
            ]);
            
            Leave::create([
                'user_id' => $user->id,
                'leave_type' => 'Casual Leave',
                'start_date' => Carbon::now()->addDays(rand(5, 10)),
                'end_date' => Carbon::now()->addDays(rand(11, 12)),
                'reason' => 'Personal work',
                'status' => 'pending',
            ]);

            // 5. Create Payroll for last month
            $lastMonth = Carbon::now()->subMonth();
            $basic = $user->employeeDetail->basic_salary;
            Payroll::create([
                'user_id' => $user->id,
                'month' => $lastMonth->format('F'),
                'year' => $lastMonth->year,
                'basic_salary' => $basic,
                'allowances' => 500,
                'deductions' => 200,
                'net_salary' => $basic + 500 - 200,
                'status' => 'paid',
            ]);
        }
    }
}
