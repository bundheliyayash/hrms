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

        // 3. Attendance Counts (Only counting completed sessions)
        $presentDates = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->whereIn('status', ['present', 'late', 'early_out']) // Include early_out as payable
            ->whereNotNull('clock_out')
            ->get()
            ->map(fn($att) => $att->date->format('Y-m-d'))
            ->toArray();
            
        $halfDayDates = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'half_day')
            ->whereNotNull('clock_out')
            ->get()
            ->map(fn($att) => $att->date->format('Y-m-d'))
            ->toArray();

        // 4. Paid vs Unpaid Leaves (Approved)
        $approvedLeaves = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($month, $year) {
                $query->whereMonth('start_date', $month)->whereYear('start_date', $year)
                      ->orWhereMonth('end_date', $month)->whereYear('end_date', $year);
            })->get();
            
        $paidLeaveDates = [];
        $unpaidLeaveDays = 0;

        foreach($approvedLeaves as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            
            $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $monthEnd = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            
            if ($end->lessThan($monthStart) || $start->greaterThan($monthEnd)) continue;
             
            $effectiveStart = $start->greaterThan($monthStart) ? $start : $monthStart;
            $effectiveEnd = $end->lessThan($monthEnd) ? $end : $monthEnd;
            
            if ($leave->is_half_day) {
                if ($leave->leave_type !== 'Unpaid Leave') {
                    $halfDayDates[] = $effectiveStart->format('Y-m-d');
                } else {
                    $unpaidLeaveDays += 0.5;
                }
            } else {
                for ($d = $effectiveStart->copy(); $d->lte($effectiveEnd); $d->addDay()) {
                    if ($leave->leave_type === 'Unpaid Leave') {
                        $unpaidLeaveDays++;
                    } else {
                        $paidLeaveDates[] = $d->format('Y-m-d');
                    }
                }
            }
        }

        // 5. Holiday Calculations
        $holidays = $this->holidayService->getHolidaysForMonth($user, $month, $year);
        $paidHolidayDates = [];
        $unpaidHolidays = 0;

        foreach ($holidays as $holiday) {
            $start = $holiday->start_date;
            $end = $holiday->end_date ?? $start;
            
            $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $monthEnd = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            
            if ($end->lessThan($monthStart) || $start->greaterThan($monthEnd)) continue;
             
            $effectiveStart = $start->greaterThan($monthStart) ? $start : $monthStart;
            $effectiveEnd = $end->lessThan($monthEnd) ? $end : $monthEnd;
            
            for ($d = $effectiveStart->copy(); $d->lte($effectiveEnd); $d->addDay()) {
                if ($holiday->type === 'unpaid') {
                    $unpaidHolidays++;
                } else {
                    $paidHolidayDates[] = $d->format('Y-m-d');
                }
            }
        }

        // 6. Payable Days - ENSURE UNIQUE DATES (Standardized Y-m-d strings)
        // Step 1: Combine all full-day credits
        $allFullCreditDates = array_unique(array_merge($presentDates, $paidLeaveDates, $paidHolidayDates));
        
        // Step 2: Remove Half-Day dates if they also exist in full credit (Attendance > Leave overlap)
        $halfDayDates = array_diff(array_unique($halfDayDates), $allFullCreditDates);
        
        $payableDays = count($allFullCreditDates) + (count($halfDayDates) * 0.5);

        // 7. Salary Calculation (Weighted Daily Proration)
        $baseEarnings = 0;
        $allPayableDates = array_unique(array_merge($allFullCreditDates, $halfDayDates));
        
        // Get all salary history applicable for this month or the last one before it
        $salaryHistory = \App\Models\SalaryHistory::where('user_id', $user->id)
            ->where('effective_date', '<=', Carbon::create($year, $month, 1)->endOfMonth())
            ->orderBy('effective_date', 'asc')
            ->get();

        foreach ($allFullCreditDates as $dateStr) {
            $date = Carbon::parse($dateStr);
            $applicableSalary = $this->getApplicableSalary($salaryHistory, $date, $basicSalary);
            $perDay = $date->daysInMonth > 0 ? ($applicableSalary / $date->daysInMonth) : 0;
            $baseEarnings += $perDay;
        }

        foreach ($halfDayDates as $dateStr) {
            $date = Carbon::parse($dateStr);
            $applicableSalary = $this->getApplicableSalary($salaryHistory, $date, $basicSalary);
            $perDay = $date->daysInMonth > 0 ? ($applicableSalary / $date->daysInMonth) : 0;
            $baseEarnings += ($perDay * 0.5);
        }

        $perDaySalary = $totalDays > 0 ? ($basicSalary / $totalDays) : 0; // Fallback for display
        // $baseEarnings = $perDaySalary * $payableDays; // Removed in favor of weighted calculation sopra

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
            'present_days' => count($presentDates),
            'half_days' => count($halfDayDates),
            'paid_leave_days' => count($paidLeaveDates),
            'unpaid_leave_days' => $unpaidLeaveDays,
            'paid_holidays' => count($paidHolidayDates),
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

    protected function getApplicableSalary($history, $date, $currentBasic)
    {
        // Find the latest revision that is <= the target date
        $revision = $history->where('effective_date', '<=', $date)->last();
        
        return $revision ? $revision->amount : $currentBasic;
    }
}
