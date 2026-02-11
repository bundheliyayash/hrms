<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\DailyAssignment;
use App\Models\ReplacementRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $manager = auth()->user();
        $date = Carbon::today()->format('Y-m-d');

        // Total staff assigned under this manager
        $staffIds = User::whereHas('employeeDetail', function($q) use ($manager) {
            $q->where('manager_id', $manager->id);
        })->pluck('id');

        $stats = [
            'total_staff' => $staffIds->count(),
            'present_today' => Attendance::whereIn('user_id', $staffIds)
                                ->where('date', $date)
                                ->whereIn('status', ['present', 'late', 'early_out'])
                                ->count(),
            'pending_replacements' => ReplacementRequest::whereHas('dailyAssignment', function($q) use ($staffIds) {
                                        $q->whereIn('user_id', $staffIds);
                                    })->where('status', 'pending')->count(),
        ];

        // Recent activity for their staff
        $recentAttendance = Attendance::with(['user', 'site'])
                            ->whereIn('user_id', $staffIds)
                            ->where('date', $date)
                            ->latest()
                            ->limit(10)
                            ->get();

        return view('manager.dashboard', compact('stats', 'recentAttendance'));
    }
}
