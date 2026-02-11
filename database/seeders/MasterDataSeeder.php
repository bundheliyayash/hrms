<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Shifts
        $shifts = [
            [
                'name' => 'Day Shift',
                'clock_in_time' => '09:00:00',
                'clock_out_time' => '18:00:00',
                'late_threshold_minutes' => 15,
                'early_out_threshold_minutes' => 15,
                'break_duration_minutes' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Night Shift',
                'clock_in_time' => '22:00:00',
                'clock_out_time' => '07:00:00',
                'late_threshold_minutes' => 15,
                'early_out_threshold_minutes' => 15,
                'break_duration_minutes' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Afternoon Shift',
                'clock_in_time' => '14:00:00',
                'clock_out_time' => '23:00:00',
                'late_threshold_minutes' => 15,
                'early_out_threshold_minutes' => 15,
                'break_duration_minutes' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Custom Site Shift',
                'clock_in_time' => '08:30:00',
                'clock_out_time' => '17:30:00',
                'late_threshold_minutes' => 10,
                'early_out_threshold_minutes' => 10,
                'break_duration_minutes' => 45,
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(['name' => $shift['name']], $shift);
        }

        // 2. Settings
        $settings = [
            'app_name' => 'ProClean HRMS',
            'company_name' => 'Bharat Facility Services Pvt Ltd',
            'company_address' => 'Floor 4, Synergy Chambers, BKC, Mumbai, Maharashtra 400051',
            'company_phone' => '+91 22 4000 1234',
            'company_email' => 'ops@bharatfacility.in',
            'currency_symbol' => '₹',
            'tax_percentage' => '18',
            'gst_number' => '27AAACB1234A1Z5',
            'pf_percentage' => '12',
            'esi_percentage' => '0.75',
        ];

        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);
        }
    }
}
