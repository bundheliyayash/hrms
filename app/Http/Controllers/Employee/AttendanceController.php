<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get month/year or date range from request
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        // Determine date range
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
        } else {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        }
        
        // Generate all dates in range, but capping at today if showing current month
        $allDates = [];
        $today = Carbon::today();
        
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            // Stop if we are generating future dates
            if ($date->gt($today)) {
                break;
            }
            $allDates[] = $date->format('Y-m-d');
        }
        
        // Reverse dates to show latest first
        $allDates = array_reverse($allDates);
        
        // Get actual attendance records
        $attendanceRecords = Attendance::with(['site', 'breaks'])
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')]) // Still fetch all for safety
            ->get()
            ->keyBy('date');
        
        // Merge all dates with attendance records
        $attendances = collect($allDates)->map(function($date) use ($attendanceRecords, $user) {
            if (isset($attendanceRecords[$date])) {
                return $attendanceRecords[$date];
            }
            
            // Create dummy record for dates without attendance
            $dummyRecord = new Attendance();
            $dummyRecord->date = $date;
            $dummyRecord->user_id = $user->id;
            $dummyRecord->status = 'absent';
            $dummyRecord->clock_in = null;
            $dummyRecord->clock_out = null;
            $dummyRecord->duration_minutes = 0;
            $dummyRecord->exists = false; // Mark as non-existent record
            
            return $dummyRecord;
        });
        
        // Paginate results (Default 7 per page)
        $perPage = $request->input('per_page', 7);
        $perPage = in_array($perPage, [7, 20, 50, 100]) ? $perPage : 7;
        $currentPage = $request->input('page', 1);
        $attendances = new \Illuminate\Pagination\LengthAwarePaginator(
            $attendances->forPage($currentPage, $perPage),
            $attendances->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Get correction requests for this period
        $corrections = \App\Models\AttendanceCorrection::where('user_id', $user->id)
            ->whereBetween('requested_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()
            ->groupBy('requested_date');
        
        // Calculate summary statistics
        $summary = $this->calculateSummary($user->id, $start, $end);
        
        // Check if employee is assigned to a site
        $assignedSite = $user->employeeDetail->site ?? null;
        
        // Get today's attendance for clock-in/out status
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->format('Y-m-d'))
            ->first();
        
        return view('employee.attendance.index', compact('attendances', 'corrections', 'summary', 'month', 'year', 'startDate', 'endDate', 'assignedSite', 'todayAttendance'));
    }
    
    private function calculateSummary($userId, $start, $end)
    {
        $records = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get();
        
        // Calculate leaves in this period
        $onLeave = \App\Models\Leave::where('user_id', $userId)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                  ->orWhereBetween('end_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
            })->count();
        
        return [
            'working_hours' => $records->sum('duration_minutes'),
            'clock_in_days' => $records->whereNotNull('clock_in')->count(),
            'late_in' => $records->where('status', 'late')->count(),
            'early_out' => $records->where('status', 'early_out')->count(),
            'on_leave' => $onLeave,
        ];
    }

    // Clock In
    public function clockIn(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $date = Carbon::today()->format('Y-m-d');

        // Rule: Only ONE attendance per day
        $existing = Attendance::where('user_id', $user->id)->where('date', $date)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'You have already clocked in for today.');
        }

        // Rule: Block clock-in if on approved leave
        $onLeave = \App\Models\Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();
        
        if ($onLeave) {
            return redirect()->back()->with('error', 'You cannot clock in while on an approved leave.');
        }

        // Check employee status
        if ($user->status !== 'active') {
            return redirect()->back()->with('error', 'Your account is currently in-active. Please contact Admin.');
        }

        // Check assigned site
        $site = $user->employeeDetail->site;

        if (!$site) {
             return redirect()->back()->with('error', 'You are not assigned to any site. Please contact Admin.');
        }

        // Check client service status
        if (!$site->client || !$site->client->isServiceActive()) {
            return redirect()->back()->with('error', 'Attendance is currently disabled for this client (Service period expired or suspended).');
        }

        // Validate Coordinates (Location is Mandatory)
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $lat1 = $request->latitude;
        $lon1 = $request->longitude;
        $lat2 = $site->latitude;
        $lon2 = $site->longitude;

        // Calculate Distance using Haversine Formula
        $distance = $this->calculateDistance($lat1, $lon1, $lat2, $lon2);

        $isVerified = $distance <= $site->radius_meters;

        $currentTime = Carbon::now();
        $status = 'present';
        $shift = $user->employeeDetail->shift;

        if ($shift) {
            $shiftStartTime = Carbon::today()->setTimeFromTimeString($shift->clock_in_time);
            $lateThreshold = $shiftStartTime->addMinutes($shift->late_threshold_minutes);
            
            if ($currentTime->greaterThan($lateThreshold)) {
                $status = 'late';
            }
        } else {
            // Fallback legacy logic
            if ($currentTime->greaterThan(Carbon::today()->setTime(10, 0, 0))) {
                $status = 'late';
            }
        }

        Attendance::create([
            'user_id' => $user->id,
            'client_id' => $site->client_id, // Link to Client
            'site_id' => $site->id,
            'date' => $date,
            'clock_in' => $currentTime->format('H:i:s'),
            'status' => $status,
            'latitude' => $lat1,
            'longitude' => $lon1,
            'is_verified' => $isVerified,
            'distance_detected' => (int)$distance,
        ]);

        $msg = 'Clock In successful at ' . $site->site_name;
        if (!$isVerified) {
            $msg .= " (Warning: Location mismatch - {$distance}m away)";
        }

        return redirect()->back()->with('success', $msg);
    }

    // Start Break
    public function startBreak()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', Carbon::today()->format('Y-m-d'))
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', 'No active attendance found to start break.');
        }

        // Check if there is already an open break
        $openBreak = $attendance->breaks()->whereNull('break_end')->first();
        if ($openBreak) {
            return redirect()->back()->with('error', 'You are already on a break.');
        }

        $breakTime = Carbon::now()->format('H:i:s');
        
        // Create break record in attendance_breaks table
        $attendance->breaks()->create([
            'break_start' => $breakTime
        ]);
        
        // Also update legacy break_start field for backward compatibility
        $attendance->update(['break_start' => $breakTime]);

        return redirect()->back()->with('success', 'Break started.');
    }

    // End Break
    public function endBreak()
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', Carbon::today()->format('Y-m-d'))
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', 'Attendance record not found.');
        }

        $openBreak = $attendance->breaks()->whereNull('break_end')->first();
        if (!$openBreak) {
            return redirect()->back()->with('error', 'No active break found to end.');
        }

        $endTime = Carbon::now();
        $startTime = Carbon::parse($openBreak->break_start);
        $duration = $startTime->diffInMinutes($endTime);

        $openBreak->update([
            'break_end' => $endTime->format('H:i:s'),
            'duration_minutes' => $duration
        ]);
        
        // Update legacy break_end field for backward compatibility
        $attendance->update(['break_end' => $endTime->format('H:i:s')]);
        
        // Recalculate attendance duration if clocked out
        if ($attendance->clock_out) {
            $clockIn = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
            $clockOut = Carbon::parse($attendance->date . ' ' . $attendance->clock_out);
            $totalMinutes = $clockIn->diffInMinutes($clockOut);
            $breakMinutes = $attendance->breaks()->sum('duration_minutes');
            $attendance->update(['duration_minutes' => max(0, $totalMinutes - $breakMinutes)]);
        }

        return redirect()->back()->with('success', 'Break ended.');
    }

    // Clock Out
    public function clockOut() 
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', Carbon::today()->format('Y-m-d'))
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', 'No active attendance found to clock out.');
        }

        // Check if on break
        $onBreak = $attendance->breaks()->whereNull('break_end')->exists();
        if ($onBreak) {
            return redirect()->back()->with('error', 'Please end your break before clocking out.');
        }
        
        $currentTime = Carbon::now();
        $clockInTime = Carbon::parse($attendance->clock_in);
        
        // Duration Calculation
        $totalMinutes = $clockInTime->diffInMinutes($currentTime);
        $totalBreakMinutes = $attendance->breaks()->sum('duration_minutes');
        
        // Net duration = Total minutes - Break minutes
        $duration = max(0, $totalMinutes - $totalBreakMinutes);
        
        // Auto Status Check (Dynamic vs Legacy)
        $status = $attendance->status;
        $shift = Auth::user()->employeeDetail->shift;

        if ($shift) {
            $shiftEndTime = Carbon::today()->setTimeFromTimeString($shift->clock_out_time);
            $earlyOutThreshold = $shiftEndTime->subMinutes($shift->early_out_threshold_minutes);
            
            if ($currentTime->lessThan($earlyOutThreshold)) {
                if ($status !== 'late') {
                    $status = 'early_out';
                }
            }
        } else {
            // Legacy fallback: 5:00 PM
            if ($currentTime->lessThan(Carbon::today()->setTime(17, 0, 0))) {
                if ($status !== 'late') {
                    $status = 'early_out';
                }
            }
        }

        $attendance->update([
            'clock_out' => $currentTime->format('H:i:s'),
            'duration_minutes' => $duration,
            'status' => $status
        ]);

        return redirect()->back()->with('success', 'Clock Out successful. Net duration: ' . floor($duration / 60) . 'h ' . ($duration % 60) . 'm');
    }

    // Store Correction Request
    public function storeCorrection(Request $request)
    {
        $request->validate([
            'requested_type' => 'required|string',
            'requested_date' => 'required|date',
            'requested_clock_in' => 'nullable',
            'requested_clock_out' => 'nullable',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $user = Auth::user();
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('corrections', 'public');
        }

        // Find associated attendance if any
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $request->requested_date)
            ->first();

        // For time_correction type, store both times in a JSON format in requested_time field
        $requestedTime = $request->requested_clock_in;
        if ($request->requested_type === 'time_correction') {
            $requestedTime = json_encode([
                'clock_in' => $request->requested_clock_in,
                'clock_out' => $request->requested_clock_out
            ]);
        }

        $correction = \App\Models\AttendanceCorrection::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id ?? null,
            'requested_type' => $request->requested_type,
            'requested_date' => $request->requested_date,
            'requested_time' => $requestedTime,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        // Send notification to all admin users
        \App\Helpers\NotificationHelper::notifyAdmins(
            'New Attendance Correction Request',
            "{$user->name} has submitted a correction request for {$request->requested_date}. Please review.",
            'warning',
            'attendance'
        );

        return redirect()->back()->with('success', 'Correction request submitted successfully. Admin will review your request.');
    }

    // Haversine Formula in PHP
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; // Meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
            
        return round($angle * $earthRadius);
    }
}
