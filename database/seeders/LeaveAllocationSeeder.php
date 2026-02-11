<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LeaveAllocation;

class LeaveAllocationSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::where('role', 'employee')->get();
        
        // Safety check: If no employees exist, skip seeding
        if ($employees->isEmpty()) {
            $this->command->warn('⚠ No employees found. Skipping leave allocation seeding.');
            return;
        }
        
        $year = date('Y');

        $leaveTypes = [
            ['type' => 'Annual Leave', 'days' => 12],
            ['type' => 'Sick Leave', 'days' => 6],
            ['type' => 'Casual Leave', 'days' => 6],
            ['type' => 'Earned Leave', 'days' => 10],
        ];

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            
            $allocatedCount = 0;
            foreach ($employees as $employee) {
                foreach ($leaveTypes as $lt) {
                    LeaveAllocation::updateOrCreate(
                        [
                            'user_id' => $employee->id,
                            'leave_type' => $lt['type'],
                            'year' => $year
                        ],
                        [
                            'total_days' => $lt['days'],
                            'used_days' => 0
                        ]
                    );
                    $allocatedCount++;
                }
            }
            
            \Illuminate\Support\Facades\DB::commit();
            $this->command->info("✓ Leave allocations seeded successfully ({$employees->count()} employees, {$allocatedCount} allocations)");
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            $this->command->error('✗ Leave allocation seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
