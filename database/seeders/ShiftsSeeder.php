<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;

class ShiftsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder populates the shifts table with default shift schedules.
     * Uses firstOrCreate() to be idempotent - safe to run multiple times.
     */
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            $shifts = [
                [
                    'name' => 'General Shift',
                    'clock_in_time' => '09:00:00',
                    'clock_out_time' => '18:00:00',
                    'late_threshold_minutes' => 15,
                    'early_out_threshold_minutes' => 15,
                    'break_duration_minutes' => 60,
                    'is_active' => true
                ],
                [
                    'name' => 'Morning Shift',
                    'clock_in_time' => '06:00:00',
                    'clock_out_time' => '14:00:00',
                    'late_threshold_minutes' => 15,
                    'early_out_threshold_minutes' => 15,
                    'break_duration_minutes' => 30,
                    'is_active' => true
                ],
                [
                    'name' => 'Evening Shift',
                    'clock_in_time' => '14:00:00',
                    'clock_out_time' => '22:00:00',
                    'late_threshold_minutes' => 15,
                    'early_out_threshold_minutes' => 15,
                    'break_duration_minutes' => 30,
                    'is_active' => true
                ],
                [
                    'name' => 'Night Shift',
                    'clock_in_time' => '22:00:00',
                    'clock_out_time' => '06:00:00',
                    'late_threshold_minutes' => 15,
                    'early_out_threshold_minutes' => 15,
                    'break_duration_minutes' => 30,
                    'is_active' => true
                ],
            ];

            // Insert each shift if it doesn't exist
            foreach ($shifts as $shift) {
                Shift::firstOrCreate(
                    ['name' => $shift['name']], // Search by name
                    $shift // Create with these values if not exists
                );
            }

            DB::commit();
            
            $this->command->info('✓ Shifts seeded successfully (' . count($shifts) . ' shifts)');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('✗ Shifts seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
