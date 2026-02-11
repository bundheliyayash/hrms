<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class HistoricalDataSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::where('role', 'employee')->with('employeeDetail.shift')->get();
        
        // 1. Create Holidays
        $holidays = [
            ['name' => 'Diwali', 'date' => '2025-11-01', 'type' => 'paid'],
            ['name' => 'Gurunanak Jayanti', 'date' => '2025-11-15', 'type' => 'paid'],
            ['name' => 'Christmas', 'date' => '2025-12-25', 'type' => 'paid'],
            ['name' => 'New Year', 'date' => '2026-01-01', 'type' => 'paid'],
            ['name' => 'Republic Day', 'date' => '2026-01-26', 'type' => 'paid'],
        ];

        foreach ($holidays as $h) {
            Holiday::updateOrCreate(['name' => $h['name'], 'start_date' => $h['date']], [
                'end_date' => $h['date'],
                'type' => $h['type'],
                'description' => 'Gazetted Holiday',
            ]);
        }

        $holidayDates = collect($holidays)->pluck('date')->toArray();

        // 2. Generate Attendance for Nov, Dec, Jan
        $start = Carbon::parse('2025-11-01');
        $end = Carbon::parse('2026-01-31');
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $isSunday = $date->isSunday();
            $isHoliday = in_array($dateStr, $holidayDates);

            foreach ($employees as $employee) {
                // Skip Sundays (Weekly Off) unless specialized
                if ($isSunday) continue;

                $detail = $employee->employeeDetail;
                if (!$detail || !$detail->site_id || !$detail->shift_id) continue;

                $status = 'present';
                $rand = rand(1, 100);

                if ($isHoliday) {
                    $status = 'holiday';
                } elseif ($rand > 95) {
                    $status = 'absent';
                } elseif ($rand > 85) {
                    $status = 'late';
                } elseif ($rand > 80) {
                    $status = 'half_day';
                }

                if ($status === 'absent') continue;

                $shift = $detail->shift;
                $clockIn = $shift->clock_in_time;
                $clockOut = $shift->clock_out_time;

                // Adjust times for variety
                if ($status === 'late') {
                    $clockIn = Carbon::parse($clockIn)->addMinutes(rand(20, 60))->format('H:i:s');
                }
                
                if ($status === 'present' && rand(1, 10) > 8) {
                    $clockOut = Carbon::parse($clockOut)->addMinutes(rand(30, 120))->format('H:i:s'); // OT
                }

                Attendance::create([
                    'user_id' => $employee->id,
                    'site_id' => $detail->site_id,
                    'client_id' => $detail->site->client_id ?? null,
                    'date' => $dateStr,
                    'clock_in' => $status === 'holiday' ? null : $clockIn,
                    'clock_out' => $status === 'holiday' ? null : $clockOut,
                    'status' => $status,
                    'is_verified' => true,
                    'latitude' => $detail->site->latitude,
                    'longitude' => $detail->site->longitude,
                ]);
            }
        }
    }
}
