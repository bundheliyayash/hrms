<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;

class LeaveSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::where('role', 'employee')->get();
        $types = ['sick', 'casual', 'earned', 'unpaid'];
        $statuses = ['approved', 'rejected', 'pending'];

        foreach ($employees->random(15) as $employee) {
            $startDate = Carbon::now()->subDays(rand(10, 80));
            $days = rand(1, 4);

            Leave::create([
                'user_id' => $employee->id,
                'leave_type' => $types[array_rand($types)],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $startDate->copy()->addDays($days)->format('Y-m-d'),
                'reason' => 'Personal work / Not feeling well',
                'status' => $statuses[array_rand($statuses)],
                'admin_comment' => 'Processed by HR',
            ]);
        }
    }
}
