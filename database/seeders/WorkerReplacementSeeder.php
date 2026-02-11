<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkerReplacement;
use App\Models\DailyAssignment;
use App\Models\User;
use Carbon\Carbon;

class WorkerReplacementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $manager = User::where('role', 'manager')->first();
        $employees = User::where('role', 'employee')->get();
        
        // Find some completed and assigned assignments
        $pastAssignments = DailyAssignment::where('status', 'completed')->take(3)->get();
        $futureAssignments = DailyAssignment::where('status', 'assigned')->take(2)->get();

        if ($pastAssignments->isEmpty() || $employees->count() < 3) return;

        // 1. Approved Replacement (Past)
        WorkerReplacement::create([
            'original_assignment_id' => $pastAssignments[0]->id,
            'original_worker_id' => $pastAssignments[0]->user_id,
            'replacement_worker_id' => $employees->last()->id,
            'site_id' => $pastAssignments[0]->site_id,
            'replacement_date' => $pastAssignments[0]->assigned_date,
            'reason' => 'absent',
            'reason_details' => 'Employee reported sick in morning. Emergency sub dispatched.',
            'status' => 'approved',
            'requested_by' => $manager->id,
            'approved_by' => $admin->id,
            'updated_at' => Carbon::now()->subDays(2)
        ]);

        // 2. Pending Replacement (Future)
        if ($futureAssignments->count() > 0) {
            WorkerReplacement::create([
                'original_assignment_id' => $futureAssignments[0]->id,
                'original_worker_id' => $futureAssignments[0]->user_id,
                'replacement_worker_id' => $employees->last()->id,
                'site_id' => $futureAssignments[0]->site_id,
                'replacement_date' => $futureAssignments[0]->assigned_date,
                'reason' => 'leave',
                'reason_details' => 'Planned vacation overlap. Requested substitute.',
                'status' => 'pending',
                'requested_by' => $manager->id
            ]);
        }

        // 3. Client Request Replacement
        if ($pastAssignments->count() > 1) {
            WorkerReplacement::create([
                'original_assignment_id' => $pastAssignments[1]->id,
                'original_worker_id' => $pastAssignments[1]->user_id,
                'replacement_worker_id' => $employees[1]->id,
                'site_id' => $pastAssignments[1]->site_id,
                'replacement_date' => $pastAssignments[1]->assigned_date,
                'reason' => 'client_request',
                'reason_details' => 'Client requested a specific specialist for deep cleaning.',
                'status' => 'approved',
                'requested_by' => $admin->id,
                'approved_by' => $admin->id,
                'updated_at' => Carbon::now()->subWeeks(1)
            ]);
        }
    }
}
