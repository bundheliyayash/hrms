<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyAssignment;
use App\Models\Contract;
use App\Models\User;
use Carbon\Carbon;

class DailyAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $employees = User::where('role', 'employee')->get();
        $contracts = Contract::with('sites')->active()->get();

        if ($employees->isEmpty() || $contracts->isEmpty()) {
            $this->command->error('Employees or Contracts not found. Run previous seeders.');
            return;
        }

        // Create assignments for the last 7 days and next 3 days
        foreach (range(-7, 3) as $dayOffset) {
            $date = Carbon::now()->addDays($dayOffset);
            
            foreach ($contracts as $contract) {
                $site = $contract->sites->first();
                if (!$site) continue;

                // Assign a subset of employees to this site for this date
                // To keep it simple, we'll assign the first N employees where N is workers_required
                $required = $site->pivot->workers_required;
                
                for ($i = 0; $i < $required && $i < $employees->count(); $i++) {
                    $employee = $employees[$i];

                    // Idempotent check: skip if already assigned to ANY site on this date
                    if (!DailyAssignment::where('user_id', $employee->id)->whereDate('assigned_date', $date)->exists()) {
                        DailyAssignment::create([
                            'user_id' => $employee->id,
                            'site_id' => $site->id,
                            'contract_id' => $contract->id,
                            'assigned_date' => $date,
                            'shift_id' => null, // Use default
                            'assigned_by' => $admin->id,
                            'status' => $date->isPast() ? 'completed' : 'assigned',
                            'assignment_type' => in_array($contract->contract_type, ['permanent', 'temporary', 'one_day']) ? $contract->contract_type : 'one_day'
                        ]);
                    }
                }
            }
        }
    }
}
