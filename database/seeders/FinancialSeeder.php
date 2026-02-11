<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payroll;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class FinancialSeeder extends Seeder
{
    public function run(): void
    {
        $employees = User::where('role', 'employee')->with('employeeDetail')->get();
        $months = [
            ['month' => '11', 'year' => 2025],
            ['month' => '12', 'year' => 2025],
            ['month' => '01', 'year' => 2026],
        ];

        foreach ($months as $m) {
            $monthStr = $m['month'];
            $year = $m['year'];

            foreach ($employees as $employee) {
                $detail = $employee->employeeDetail;
                if (!$detail) continue;

                // Simple Logic: Present days from attendance
                $presentDays = Attendance::where('user_id', $employee->id)
                    ->whereMonth('date', $monthStr)
                    ->whereYear('date', $year)
                    ->whereIn('status', ['present', 'late', 'half_day', 'holiday'])
                    ->count();

                if ($presentDays == 0) continue;

                $gross = $detail->basic_salary;
                $perDay = $gross / 30;
                
                $payableDays = $presentDays; // Simplified
                $basic = $gross * 0.5;
                $allowances = $gross * 0.5; // Combined HRA and others into allowances
                
                $otHours = rand(0, 1) ? rand(5, 20) : 0;
                $otAmount = $otHours * ($perDay / 8) * 1.5;
                
                $payableSalary = $perDay * $payableDays;
                
                $pf = $basic * 0.12;
                $esi = $payableSalary * 0.0075;
                $deductions = $pf + $esi + 200; // Including PT

                $net = $payableSalary + $otAmount - $deductions;

                Payroll::create([
                    'user_id' => $employee->id,
                    'month' => $monthStr,
                    'year' => $year,
                    'total_days' => 30,
                    'working_days' => 26,
                    'present_days' => $presentDays,
                    'payable_days' => $payableDays,
                    'per_day_salary' => $perDay,
                    'ot_hours' => $otHours,
                    'ot_amount' => $otAmount,
                    'basic_salary' => $basic,
                    'allowances' => $allowances,
                    'deductions' => $deductions,
                    'pf_amount' => $pf,
                    'esi_amount' => $esi,
                    'net_salary' => $net,
                    'status' => 'paid',
                ]);
            }
        }
    }
}
