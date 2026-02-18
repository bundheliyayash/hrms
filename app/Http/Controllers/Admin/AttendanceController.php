<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance today.
     */
    public function index(Request $request)
    {
        $date = $request->date ?? Carbon::today()->format('Y-m-d');
        $user = auth()->user();

        // If it's a specific employee view
        if ($request->has('user_id')) {
            return $this->showEmployee($request);
        }

        // Otherwise show daily list
        $query = Attendance::with(['user', 'site', 'breaks'])->where('date', $date);

        if ($user->role === 'manager') {
            $query->whereHas('user.employeeDetail', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }

        $attendances = $query->paginate(20);

        // Fetch Employee List for "Client Wise" tab
        $employeeQuery = User::where('role', 'employee')->with(['employeeDetail.site.client']);
        if ($user->role === 'manager') {
            $employeeQuery->whereHas('employeeDetail', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }
        $employees = $employeeQuery->get();

        return view('admin.attendance.index', compact('attendances', 'employees', 'date'));
    }

    /**
     * Show professional timesheet for a specific employee.
     */
    public function showEmployee(Request $request)
    {
        $targetUser = User::findOrFail($request->user_id);
        
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $month = $request->get('month');
        $year = $request->get('year');

        if ($startDate && $endDate) {
            // Custom Range View
            $month = null;
            $year = null;
        } else {
            // Month View (Default or requested)
            $defaultMonth = date('m');
            $defaultYear = date('Y');
            if (!$month && !$request->has('start_date') && date('d') <= 7) {
                $lastMonth = Carbon::now()->subMonth();
                $defaultMonth = $lastMonth->format('m');
                $defaultYear = $lastMonth->format('Y');
            }

            $month = $month ?? $defaultMonth;
            $year = $year ?? $defaultYear;
            
            $dateRange = Carbon::create($year, $month, 1);
            $startDate = $dateRange->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $dateRange->copy()->endOfMonth()->format('Y-m-d');
        }

        $attendances = Attendance::where('user_id', $targetUser->id)
                                 ->whereBetween('date', [$startDate, $endDate])
                                 ->with(['breaks', 'site.client'])
                                 ->orderBy('date', 'desc')
                                 ->get();

        // ---------------------------------------------------------
        // GAP FILLING LOGIC (Standardized Y-m-d keys)
        // ---------------------------------------------------------
        $attendanceMap = $attendances->keyBy(fn($record) => $record->date->format('Y-m-d'));
        $allDates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $holidayService = new \App\Services\HolidayService();

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            
            if ($attendanceMap->has($dateStr)) {
                $allDates[] = $attendanceMap->get($dateStr);
            } else {
                // Determine virtual status
                $status = 'absent';
                $isHoliday = $holidayService->isHoliday($targetUser, $dateStr);
                
                if ($isHoliday || $current->isSunday()) {
                    $status = 'holiday';
                }

                $virtual = new Attendance([
                    'user_id' => $targetUser->id,
                    'date' => $dateStr,
                    'status' => $status,
                ]);
                $virtual->setRelation('breaks', collect());
                $allDates[] = $virtual;
            }
            $current->addDay();
        }

        // Re-sort descending (latest first as per UI standard)
        $attendances = collect($allDates)->sortByDesc('date');

        // Summary Calculations
        $summary = [
            'working_hours' => 0,
            'productive_minutes' => 0,
            'clock_in_days' => 0,
            'late_in' => 0,
            'early_out' => 0,
            'absent' => 0,
            'on_leave' => 0,
            'holiday' => 0,
        ];

        // Recalculate Summary (Actual + Virtual)
        foreach ($attendances as $att) {
            if ($att->id) {
                $summary['working_hours'] += $att->duration_minutes;
                $summary['clock_in_days']++;
                if ($att->status === 'late') $summary['late_in']++;
                if ($att->status === 'early_out') $summary['early_out']++;
            }
            
            if ($att->status === 'absent') $summary['absent']++;
            if ($att->status === 'holiday') $summary['holiday']++;
        }

        return view('admin.attendance.employee_view', compact('attendances', 'targetUser', 'summary', 'month', 'year', 'startDate', 'endDate'));
    }

    /**
     * Handle manual record creation or update.
     */
    public function update(Request $request, $id)
    {
        // Check if $id is numeric (attendance ID) or a date string (Y-m-d)
        $attendance = is_numeric($id) ? Attendance::find($id) : null;
        $date = !is_numeric($id) ? $id : null;
        $userId = $request->user_id;

        if ($attendance && $attendance->is_locked) {
            return redirect()->back()->with('error', 'This attendance record is locked and cannot be edited.');
        }

        $request->validate([
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'status' => 'required|in:present,absent,late,early_out,half_day,missing,holiday',
            'reason' => 'required|string|max:255',
        ]);

        if ($attendance) {
            $attendance->update([
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
                'status' => $request->status,
            ]);
            if ($attendance->clock_in && $attendance->clock_out) {
                $attendance->update(['duration_minutes' => $attendance->duration]);
            }
        } else {
            // Create new record for the date
            $user = User::findOrFail($userId);
            $site = $user->employeeDetail->site;
            
            $attendance = Attendance::create([
                'user_id' => $userId,
                'date' => $date,
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
                'status' => $request->status,
                'client_id' => $site->client_id ?? null,
                'site_id' => $site->id ?? null,
                'is_verified' => true,
            ]);
            if ($attendance->clock_in && $attendance->clock_out) {
                $attendance->update(['duration_minutes' => $attendance->duration]);
            }
        }

        \App\Helpers\NotificationHelper::send(
            $attendance->user_id,
            'Attendance Updated by Admin',
            "Your attendance for {$attendance->date} has been updated by " . auth()->user()->name . ". Reason: {$request->reason}",
            'info',
            'attendance'
        );

        return redirect()->back()->with('success', 'Attendance record saved and employee notified.');
    }
    public function requests()
    {
        $user = auth()->user();
        $query = AttendanceCorrection::with(['user', 'attendance'])->where('status', 'pending');

        if ($user->role === 'manager') {
            $query->whereHas('user.employeeDetail', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }

        $requests = $query->latest()->paginate(20);

        return view('admin.attendance.requests', compact('requests'));
    }

    /**
     * Handle correction request decision.
     */
    public function handleRequest(Request $request, AttendanceCorrection $correction)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_remark' => 'nullable|string|max:500',
        ]);

        $handler = auth()->user();

        $correction->update([
            'status' => $request->status,
            'admin_remark' => $request->admin_remark,
            'handled_by' => $handler->id,
        ]);

        if ($request->status === 'approved') {
            $attendance = $correction->attendance;

            if ($attendance) {
                // Determine what to update for existing attendance
                $updateData = [];
                
                if ($correction->requested_type === 'time_correction') {
                    // Parse JSON time data
                    $times = json_decode($correction->requested_time, true);
                    if ($times) {
                        $updateData['clock_in'] = $times['clock_in'];
                        $updateData['clock_out'] = $times['clock_out'];
                    }
                } else {
                    // Handle other request types
                    switch ($correction->requested_type) {
                        case 'clock_in':
                            $updateData['clock_in'] = $correction->requested_time;
                            break;
                        case 'clock_out':
                            $updateData['clock_out'] = $correction->requested_time;
                            break;
                    }
                }

                if (!empty($updateData)) {
                    $attendance->update($updateData);
                    
                    // Recalculate duration if both times are present
                    if ($attendance->clock_in && $attendance->clock_out) {
                        $attendance->update(['duration_minutes' => $attendance->duration]);
                    }
                }
            } else {
                // No attendance record exists - create new one
                $clockIn = '09:00:00';
                $clockOut = null;
                
                if ($correction->requested_type === 'time_correction') {
                    $times = json_decode($correction->requested_time, true);
                    if ($times) {
                        $clockIn = $times['clock_in'];
                        $clockOut = $times['clock_out'];
                    }
                } else {
                    $clockIn = $correction->requested_type === 'clock_in' || $correction->requested_type === 'full_day' 
                        ? $correction->requested_time 
                        : '09:00:00';
                        
                    $clockOut = $correction->requested_type === 'clock_out' || $correction->requested_type === 'full_day'
                        ? $correction->requested_time
                        : null;
                    
                    if ($correction->requested_type === 'full_day') {
                        $clockIn = '09:00:00';
                        $clockOut = $correction->requested_time;
                    }
                }
                
                // Get employee's site
                $employeeSite = $correction->user->employeeDetail->site ?? null;
                
                $newAttendance = Attendance::create([
                    'user_id' => $correction->user_id,
                    'date' => $correction->requested_date,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'status' => 'present',
                    'is_verified' => true,
                    'client_id' => $employeeSite->client_id ?? null,
                    'site_id' => $employeeSite->id ?? null,
                ]);
                
                // Calculate duration if both times present
                if ($clockIn && $clockOut) {
                    $newAttendance->update(['duration_minutes' => $newAttendance->duration]);
                }
            }
        }

        // Notification for Employee
        \App\Helpers\NotificationHelper::send(
            $correction->user_id,
            'Attendance Correction ' . ucfirst($request->status),
            "Your correction request for {$correction->requested_date} has been " . $request->status . " by {$handler->name}.",
            $request->status === 'approved' ? 'success' : 'danger',
            'attendance'
        );

        return redirect()->back()->with('success', 'Request ' . $request->status . ' successfully.');
    }


    /**
     * Lock Attendance
     */
    public function lock(Attendance $attendance)
    {
        $attendance->update(['is_locked' => true]);
        return redirect()->back()->with('success', 'Attendance record locked.');
    }

    /**
     * Export Attendance to CSV
     */
    public function export(Request $request)
    {
        $date = $request->date ?? Carbon::today()->format('Y-m-d');
        $attendances = Attendance::with(['user', 'site'])->where('date', $date)->get();

        $filename = "attendance_export_" . $date . ".csv";
        $handle = fopen('php://memory', 'w');
        
        // CSV Headers
        fputcsv($handle, ['Employee Name', 'Client', 'Site', 'Date', 'Clock In', 'Clock Out', 'Duration (Min)', 'Status', 'Verified']);

        foreach ($attendances as $att) {
            fputcsv($handle, [
                $att->user->name,
                $att->site->client->name ?? 'N/A',
                $att->site->site_name ?? 'N/A',
                $att->date,
                $att->clock_in,
                $att->clock_out ?? '-',
                $att->duration_minutes,
                strtoupper($att->status),
                $att->is_verified ? 'YES' : 'NO'
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}")
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
