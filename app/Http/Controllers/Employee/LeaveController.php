<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Mail\LeaveRequested;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LeaveController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $leaves = Leave::where('user_id', $userId)->orderBy('created_at', 'desc')->paginate(10);
        $allocations = \App\Models\LeaveAllocation::where('user_id', $userId)->where('year', date('Y'))->get();
        
        return view('employee.leaves.index', compact('leaves', 'allocations'));
    }

    public function create()
    {
        return view('employee.leaves.create');
    }

    public function store(Request $request)
    {
        $isHalfDay = $request->has('is_half_day');
        
        $rules = [
            'leave_type' => 'required|string|in:Annual Leave,Sick Leave,Casual Leave,Earned Leave,Unpaid Leave',
            'reason' => 'required|string|max:500',
        ];
        
        if ($isHalfDay) {
            $rules['half_day_date'] = 'required|date|after_or_equal:today';
            $rules['half_day_period'] = 'required|in:morning,afternoon';
        } else {
            $rules['start_date'] = 'required|date|after_or_equal:today';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
        }
        
        $validated = $request->validate($rules);

        // Calculate requested days
        if ($isHalfDay) {
            $requestedDays = 0.5;
            $startDate = $validated['half_day_date'];
            $endDate = $validated['half_day_date'];
        } else {
            $start = \Carbon\Carbon::parse($validated['start_date']);
            $end = \Carbon\Carbon::parse($validated['end_date']);
            $requestedDays = $start->diffInDays($end) + 1;
            $startDate = $validated['start_date'];
            $endDate = $validated['end_date'];
        }

        // Check balance for paid leaves
        if ($validated['leave_type'] !== 'Unpaid Leave') {
            $allocation = \App\Models\LeaveAllocation::where('user_id', Auth::id())
                ->where('leave_type', $validated['leave_type'])
                ->where('year', date('Y'))
                ->first();

            if (!$allocation || ($allocation->total_days - $allocation->used_days) < $requestedDays) {
                return redirect()->back()->with('error', 'Insufficient leave balance for ' . $validated['leave_type'] . '. Available: ' . ($allocation ? $allocation->total_days - $allocation->used_days : 0) . ' days.');
            }
        }

        $leave = Leave::create([
            'user_id' => Auth::id(),
            'leave_type' => $validated['leave_type'],
            'reason' => $validated['reason'],
            'is_half_day' => $isHalfDay,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'half_day_period' => $isHalfDay ? $validated['half_day_period'] : null,
            'status' => 'pending',
        ]);
        
        // Send notification to admin
        \App\Helpers\NotificationHelper::notifyAdmins(
            'New Leave Request',
            Auth::user()->name . " has applied for {$requestedDays} day(s) of {$validated['leave_type']}. Please review.",
            'info',
            'leaves'
        );

        // Send email to all admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new LeaveRequested($leave, Auth::user()));
        }

        return redirect()->route('employee.leaves.index')->with('success', 'Leave application submitted successfully. Awaiting approval.');
    }
}
