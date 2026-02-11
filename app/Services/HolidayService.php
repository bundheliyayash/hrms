<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;

class HolidayService
{
    /**
     * Check if a specific date is a holiday for a specific user.
     */
    public function isHoliday(User $user, $date)
    {
        $date = Carbon::parse($date);
        
        return Holiday::where('status', 'active')
            ->where('start_date', '<=', $date->format('Y-m-d'))
            ->where('end_date', '>=', $date->format('Y-m-d'))
            ->where(function ($query) use ($user) {
                $query->where('applicable_type', 'all')
                    ->orWhere(function ($q) use ($user) {
                        $q->where('applicable_type', 'department')
                            ->whereHas('departments', function ($dq) use ($user) {
                                $dq->where('department', $user->employeeDetail->department ?? '');
                            });
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where('applicable_type', 'site')
                            ->whereHas('sites', function ($sq) use ($user) {
                                $sq->where('client_sites.id', $user->employeeDetail->site_id ?? 0);
                            });
                    });
            })
            ->first();
    }

    /**
     * Get all holidays for a month/year applicable to a user.
     */
    public function getHolidaysForMonth(User $user, $month, $year)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        return Holiday::where('status', 'active')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->where(function ($query) use ($user) {
                $query->where('applicable_type', 'all')
                    ->orWhere(function ($q) use ($user) {
                        $q->where('applicable_type', 'department')
                            ->whereHas('departments', function ($dq) use ($user) {
                                $dq->where('department', $user->employeeDetail->department ?? '');
                            });
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where('applicable_type', 'site')
                            ->whereHas('sites', function ($sq) use ($user) {
                                $sq->where('client_sites.id', $user->employeeDetail->site_id ?? 0);
                            });
                    });
            })
            ->get();
    }
}
