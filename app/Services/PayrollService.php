<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use Carbon\Carbon;
use App\Models\Setting;

class PayrollService
{
    protected $holidayService;

    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    public function calculateSalary(User $user, $month, $year)
    {
        $user->load('employeeDetail');
        
        $date = Carbon::createFromDate($year, $month, 1);
        
        // 1. Basic Salary
        $basicSalary = $user->employeeDetail->basic_salary ?? 0;

        // 2. Days in Month
        $totalDays = $date->daysInMonth;

        // 3. Attendance Counts
        $present = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->whereIn('status', ['present', 'late'])
            ->count();
            
        $halfDays = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'half_day')
            ->count();

        // 4. Paid vs Unpaid Leaves (Approved)
        $approvedLeaves = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($month, $year) {
                $query->whereMonth('start_date', $month)->whereYear('start_date', $year)
                      ->orWhereMonth('end_date', $month)->whereYear('end_date', $year);
            })->get();
            
        $paidLeaveDays = 0;
        $unpaidLeaveDays = 0;

        foreach($approvedLeaves as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            
            $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $monthEnd = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            
            if ($end->lessThan($monthStart) || $start->greaterThan($monthEnd)) continue;
             
            $effectiveStart = $start->greaterThan($monthStart) ? $start : $monthStart;
            $effectiveEnd = $end->lessThan($monthEnd) ? $end : $monthEnd;
            
            $days = $leave->is_half_day ? 0.5 : ($effectiveStart->diffInDays($effectiveEnd) + 1);

            if ($leave->leave_type === 'Unpaid Leave') {
                $unpaidLeaveDays += $days;
            } else {
                $paidLeaveDays += $days;
            }
        }

        // 5. Holiday Calculations
        $holidays = $this->holidayService->getHolidaysForMonth($user, $month, $year);
        $paidHolidays = 0;
        $unpaidHolidays = 0;

        foreach ($holidays as $holiday) {
            $start = $holiday->start_date;
            $end = $holiday->end_date ?? $start;
            
            $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $monthEnd = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            
            if ($end->lessThan($monthStart) || $start->greaterThan($monthEnd)) continue;
             
            $effectiveStart = $start->greaterThan($monthStart) ? $start : $monthStart;
            $effectiveEnd = $end->lessThan($monthEnd) ? $end : $monthEnd;
            
            $days = $effectiveStart->diffInDays($effectiveEnd) + 1;

            if ($holiday->type === 'unpaid') {
                $unpaidHolidays += $days;
            } else {
                $paidHolidays += $days;
            }
        }

        // 6. Payable Days
        // Formula: Present + (HalfDay * 0.5) + PaidLeaves + PaidHolidays
        $payableDays = $present + ($halfDays * 0.5) + $paidLeaveDays + $paidHolidays;

        // 7. Salary Calculation
        $perDaySalary = $totalDays > 0 ? ($basicSalary / $totalDays) : 0;
        $baseEarnings = $perDaySalary * $payableDays;

        // 8. Overtime (OT) Calculation
        $totalOtHours = 0;
        $otEarnings = 0;

        $attendances = Attendance::with(['user', 'site'])
            ->where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        foreach ($attendances as $att) {
            // Lazy load to avoid SQLite column issue in tests
            $assignment = \App\Models\DailyAssignment::where('user_id', $att->user_id)
                ->whereDate('assigned_date', $att->date)
                ->where('site_id', $att->site_id)
                ->with(['contract', 'shift']) // Eager load nested relations
                ->first();

            if (!$assignment || !$assignment->contract || !$assignment->contract->ot_enabled) continue;

            $contract = $assignment->contract;
            $shift = $assignment->shift;
            
            if (!$shift) continue;

            $scheduledMinutes = $shift->getDurationMinutes();
            $allowedThreshold = $contract->calculate_ot_after_minutes;
            $actualMinutes = $att->duration_minutes;

            if ($actualMinutes > ($scheduledMinutes + $allowedThreshold)) {
                $extraMinutes = $actualMinutes - $scheduledMinutes;
                $extraHours = $extraMinutes / 60;
                $totalOtHours += $extraHours;

                // OT Pay = (Per Hour Salary * OT Multiplier) * Extra Hours
                $perHourSalary = $perDaySalary / 8; // Assuming 8-hour day for rate calc
                $otRate = $perHourSalary * $contract->ot_multiplier;
                $otEarnings += ($otRate * $extraHours);
            }
        }

        // 9. Statutory Deductions (Deducted from Base Earnings + OT)
        $grossTotal = $baseEarnings + $otEarnings;
        $pfPercentage = Setting::where('key', 'pf_percentage')->first()->value ?? 12;
        $esiPercentage = Setting::where('key', 'esi_percentage')->first()->value ?? 0.75;

        $pfAmount = round(($grossTotal * $pfPercentage) / 100, 2);
        $esiAmount = round(($grossTotal * $esiPercentage) / 100, 2);

        return [
            'basic_salary' => $basicSalary,
            'total_days' => $totalDays,
            'present_days' => $present,
            'half_days' => $halfDays,
            'paid_leave_days' => $paidLeaveDays,
            'unpaid_leave_days' => $unpaidLeaveDays,
            'paid_holidays' => $paidHolidays,
            'unpaid_holidays' => $unpaidHolidays,
            'payable_days' => $payableDays,
            'per_day_salary' => round($perDaySalary, 2),
            'base_earnings' => round($baseEarnings, 2),
            'ot_hours' => round($totalOtHours, 2),
            'ot_earnings' => round($otEarnings, 2),
            'pf_amount' => $pfAmount,
            'esi_amount' => $esiAmount,
        ];
    }
}
