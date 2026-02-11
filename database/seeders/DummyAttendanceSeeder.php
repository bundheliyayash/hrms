<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DummyAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // Get employees
        $employees = DB::table('users')->where('role', 'employee')->get();
        if ($employees->isEmpty()) {
            $this->command->error('No employees found. Please create some first.');
            return;
        }

        // Get sites
        $sites = DB::table('client_sites')->get();
        if ($sites->isEmpty()) {
            $this->command->error('No sites found. Please create some first.');
            return;
        }

        $startDate = Carbon::now()->subMonths(3)->startOfMonth();
        $endDate = Carbon::now();

        $this->command->info("Seeding data for 3 months (Oct 2025 - Jan 2026)...");

        foreach ($employees as $employee) {
            $site = $sites->random();
            $this->command->info("Seeding for employee: {$employee->name} (Site: {$site->site_name})");

            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                if ($date->gt(Carbon::now())) break;
                if ($date->isSunday()) continue;

                // Randomly mark as absent (10% chance)
                $status = (rand(1, 10) === 1) ? 'absent' : 'present';
                
                $attendanceData = [
                    'user_id' => $employee->id,
                    'site_id' => $site->id,
                    'client_id' => $site->client_id,
                    'date' => $date->format('Y-m-d'),
                    'status' => $status,
                    'latitude' => $site->latitude,
                    'longitude' => $site->longitude,
                    'is_locked' => 0,
                    'is_verified' => ($status === 'present' ? 1 : 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($status === 'present') {
                    $clockIn = $date->copy()->setTime(rand(8, 9), rand(0, 59), 0);
                    $clockOut = $date->copy()->setTime(rand(17, 18), rand(0, 59), 0);
                    
                    $attendanceData['clock_in'] = $clockIn->toTimeString();
                    $attendanceData['clock_out'] = $clockOut->toTimeString();
                    
                    // Logic for 'late' status
                    if ($clockIn->hour >= 9 && $clockIn->minute > 15) {
                        $attendanceData['status'] = 'late';
                    }

                    $breakMin = rand(30, 60);
                    $workMin = $clockIn->diffInMinutes($clockOut) - $breakMin;
                    
                    $attendanceData['total_break_minutes'] = $breakMin;
                    $attendanceData['duration_minutes'] = $workMin;
                    
                    $attendanceData['break_start'] = '13:00:00';
                    $attendanceData['break_end'] = $date->copy()->setTime(13, 0, 0)->addMinutes($breakMin)->toTimeString();
                } else {
                    $attendanceData['total_break_minutes'] = 0;
                    $attendanceData['duration_minutes'] = 0;
                }

                try {
                    // Delete existing to avoid conflicts
                    DB::table('attendances')->where('user_id', $employee->id)->where('date', $date->format('Y-m-d'))->delete();
                    
                    $attendanceId = DB::table('attendances')->insertGetId($attendanceData);

                    if ($status === 'present') {
                        DB::table('attendance_breaks')->insert([
                            'attendance_id' => $attendanceId,
                            'break_start' => '13:00:00',
                            'break_end' => $date->copy()->setTime(13, 0, 0)->addMinutes($breakMin)->toTimeString(),
                            'duration_minutes' => $breakMin,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->command->error("Error seeding {$date->toDateString()}: " . $e->getMessage());
                }
            }
        }

        $this->command->info('Seeding completed successfully!');
    }
}
