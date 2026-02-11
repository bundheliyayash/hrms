<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Phase2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Clients & Sites
        $client1 = \App\Models\Client::firstOrCreate(
            ['email' => 'contact@techsolutions.com'],
            [
                'name' => 'TechSolutions Inc',
                'phone' => '9876543210',
                'address' => '123 Tech Park, NY'
            ]
        );

        $site1 = \App\Models\ClientSite::firstOrCreate(
            ['site_name' => 'Headquarters', 'client_id' => $client1->id],
            [
                'address' => 'Tech Park, Main Block',
                'latitude' => '40.712800',
                'longitude' => '-74.006000',
                'radius_meters' => 200
            ]
        );

        $client2 = \App\Models\Client::firstOrCreate(
            ['email' => 'admin@megacorp.com'],
            [
                'name' => 'MegaCorp',
                'phone' => '1122334455',
                'address' => '456 Mega Towers, LA'
            ]
        );

        $site2 = \App\Models\ClientSite::firstOrCreate(
             ['site_name' => 'LA Branch', 'client_id' => $client2->id],
             [
                'address' => 'Downtown LA',
                'latitude' => '34.052200',
                'longitude' => '-118.243700',
                'radius_meters' => 150
            ]
        );

        // 2. Create Employees & Assign to Sites
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = \App\Models\User::firstOrCreate(
                ['email' => 'staff' . $i . '@hrms.com'],
                [
                    'name' => 'Field Staff ' . $i,
                    'password' => bcrypt('password'),
                    'role' => 'employee'
                ]
            );

            \App\Models\EmployeeDetail::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => 'EMP-00' . $i,
                    'phone' => '900000000' . $i,
                    'department' => 'Operations',
                    'designation' => 'Field Officer',
                    'basic_salary' => 50000,
                    'joining_date' => now()->subMonths(6),
                    'site_id' => $i % 2 == 0 ? $site2->id : $site1->id 
                ]
            );
            $users[] = $user;
        }

        // 3. Create Attendance for Current Month (for Reports)
        $month = now()->month;
        $year = now()->year;
        $daysInMonth = now()->day; // Up to today

        foreach ($users as $user) {
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date = \Carbon\Carbon::createFromDate($year, $month, $d);
                if ($date->isWeekend()) continue;

                // Check if attendance exists
                if (\App\Models\Attendance::where('user_id', $user->id)->where('date', $date->format('Y-m-d'))->exists()) {
                    continue;
                }

                $status = rand(0, 10);
                $attStatus = 'present';
                if ($status > 8) $attStatus = 'absent';
                elseif ($status == 8) $attStatus = 'late';
                elseif ($status == 7) $attStatus = 'half_day';

                if ($attStatus != 'absent') {
                    \App\Models\Attendance::create([
                        'user_id' => $user->id,
                        'date' => $date->format('Y-m-d'),
                        'clock_in' => '09:00:00',
                        'clock_out' => '18:00:00',
                        'status' => $attStatus,
                        'site_id' => $user->employeeDetail->site_id,
                        'is_verified' => true,
                        'latitude' => '0',
                        'longitude' => '0',
                        'distance_detected' => 0
                    ]);
                } else {
                     \App\Models\Attendance::create([
                        'user_id' => $user->id,
                        'date' => $date->format('Y-m-d'),
                        'status' => 'absent',
                    ]);
                }
            }
        }
    }
}
