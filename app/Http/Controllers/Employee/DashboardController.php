<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');
        
        // 1. Today's Status
        $todayAttendance = Attendance::with('breaks')
                                     ->where('user_id', $user->id)
                                     ->where('date', $today)
                                     ->first();

        // 2. Monthly Summary Stats (Normalized)
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $monthlyStats = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy(fn($att) => strtolower($att->status))
            ->map(fn($group) => $group->count());
            
        $present = $monthlyStats['present'] ?? 0;
        $late = $monthlyStats['late'] ?? 0;
        $absent = $monthlyStats['absent'] ?? 0;
        $half_day = $monthlyStats['half_day'] ?? 0;
        $early_out = $monthlyStats['early_out'] ?? 0;
        
        // 3. Chart Data (All days from 1st to current date of month)
        $chartLabels = [];
        $chartData = [];
        $startOfMonth = Carbon::now()->startOfMonth();
        $today = Carbon::today();
        
        // Loop through all days from 1st to today
        for ($date = $startOfMonth->copy(); $date->lte($today); $date->addDay()) {
            $chartLabels[] = $date->format('d M');
            
            $att = Attendance::where('user_id', $user->id)
                             ->where('date', $date->format('Y-m-d'))
                             ->first();
                             
            if ($att && $att->clock_out && $att->clock_in) {
                // Calculate hours worked using duration_minutes
                $hours = round($att->duration_minutes / 60, 1);
                $chartData[] = $hours;
            } else {
                $chartData[] = 0; // No attendance or incomplete
            }
        }

        // 4. Assigned Site
        $assignedSite = $user->employeeDetail->site ?? null;

        return view('employee.dashboard', compact(
            'todayAttendance', 
            'present', 'late', 'absent', 'half_day', 'early_out',
            'chartLabels', 'chartData',
            'assignedSite'
        ));
    }
}
