<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Handle the role-based dashboard redirection.
     */
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if ($user->role === 'employee') {
            return redirect()->route('employee.dashboard');
        }

        // 1. Core Stats
        $totalEmployees = \App\Models\User::where('role', '!=', 'admin')->count();
        $activeEmployees = \App\Models\User::where('role', '!=', 'admin')->where('status', 'active')->count();
        
        $onRollCount = \App\Models\EmployeeDetail::where('employment_type', 'On-roll')->count();
        $tempCount = \App\Models\EmployeeDetail::where('employment_type', 'Temporary')->count();

        $today = \Carbon\Carbon::today()->format('Y-m-d');
        $presentToday = \App\Models\Attendance::where('date', $today)->whereIn('status', ['present', 'late', 'half_day'])->count();
        $lateToday = \App\Models\Attendance::where('date', $today)->where('status', 'late')->count();
        $absentToday = $activeEmployees - $presentToday;
        $attendanceRate = $activeEmployees > 0 ? round(($presentToday / $activeEmployees) * 100) : 0;

        $pendingCorrections = \App\Models\AttendanceCorrection::where('status', 'pending')->count();
        $pendingLeaves = \App\Models\Leave::where('status', 'pending')->count();
        
        // Active Clients (Considering toggle and service dates)
        $clients = \App\Models\Client::where('is_active', true)->get();
        $activeClientsCount = $clients->filter(fn($c) => $c->isServiceActive())->count();
        
        // Sum of paid payroll for current month
        $currentMonth = \Carbon\Carbon::now()->format('F');
        $currentYear = \Carbon\Carbon::now()->format('Y');
        $monthlyPayroll = \App\Models\Payroll::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->sum('net_salary');
            
        $locationAlerts = \App\Models\Attendance::where('date', $today)
            ->where('is_verified', false)
            ->count();

        $stats = [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'on_roll' => $onRollCount,
            'temporary' => $tempCount,
            'attendance_rate' => $attendanceRate,
            'present_count' => $presentToday,
            'late_count' => $lateToday,
            'absent_count' => max(0, $absentToday),
            'pending_requests' => $pendingCorrections + $pendingLeaves,
            'active_clients' => $activeClientsCount,
            'monthly_payroll' => number_format($monthlyPayroll / 100000, 1) . 'L',
            'location_alerts' => $locationAlerts
        ];

        // 2. Recent Attendance Feed (Paginated)
        $recentAttendance = \App\Models\Attendance::with(['user.employeeDetail', 'site.client'])
            ->where('date', $today)
            ->latest()
            ->paginate(5);

        // 3. Alerts
        $suspiciousPunchesQuery = \App\Models\Attendance::where('date', $today)
            ->where('is_verified', false)
            ->with(['user', 'site.client']);
            
        $suspiciousPunches = $suspiciousPunchesQuery->get()
            ->filter(function($punch) {
                return $punch->site && $punch->site->client && $punch->site->client->isServiceActive();
            })
            ->take(5);

        $alerts = [];
        foreach ($suspiciousPunches as $punch) {
            $alerts[] = [
                'type' => 'Location Mismatch',
                'user' => $punch->user ? $punch->user->name : 'Unknown',
                'msg' => 'punched in from unauthorized location (' . $punch->distance_detected . 'm away)',
                'time' => $punch->created_at->diffForHumans(),
                'color' => 'danger'
            ];
        }

        if ($pendingLeaves > 0) {
            $alerts[] = [
                'type' => 'Leave Requests',
                'user' => $pendingLeaves . ' employees',
                'msg' => 'applied for leaves needing approval',
                'time' => 'Action required',
                'color' => 'warning'
            ];
        }

        // 4. Site Distribution (Filter inactive clients)
        $sitesData = \App\Models\ClientSite::with(['client', 'employees'])->get()
            ->filter(function($site) {
                return $site->is_active && $site->client && $site->client->isServiceActive();
            });
            
        $sites = $sitesData->map(function($site) {
            return [
                'name' => $site->site_name,
                'location' => $site->address ?? 'Site Location',
                'count' => $site->employees->count()
            ];
        });

        // 5. Weekly Attendance Trend (for Line Chart)
        $chartLabels = [];
        $chartPresent = [];
        $chartLate = [];
        $chartAbsent = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::today()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $chartLabels[] = $date->format('D');
            
            $p = \App\Models\Attendance::where('date', $dateStr)->whereIn('status', ['present', 'late', 'half_day'])->count();
            $l = \App\Models\Attendance::where('date', $dateStr)->where('status', 'late')->count();
            $a = max(0, $totalEmployees - $p);
            
            $chartPresent[] = $p;
            $chartLate[] = $l;
            $chartAbsent[] = $a;
        }

        $chartData = [
            'labels' => $chartLabels,
            'present' => $chartPresent,
            'late' => $chartLate,
            'absent' => $chartAbsent
        ];

        return view('dashboard', compact('stats', 'recentAttendance', 'alerts', 'sites', 'chartData'));
    }
}
