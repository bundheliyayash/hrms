<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index()
    {
        $leaves = Leave::with('user')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.leaves.index', compact('leaves'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_comment' => 'nullable|string|max:500',
        ]);

        $leave = Leave::findOrFail($id);
        $oldStatus = $leave->status;

        // Security Fix: Prevent self-approval
        if ($leave->user_id == auth()->id()) {
            return redirect()->back()->with('error', 'You cannot approve or reject your own leave request.');
        }

        // Hard Lock Check: Prevent editing locked records
        if ($leave->is_locked) {
            return redirect()->back()->with('error', 'This leave record is locked (Payroll Finalized) and cannot be modified.');
        }

        $leave->update([
            'status' => $request->status,
            'admin_comment' => $request->admin_comment
        ]);

        // Calculate days for balance adjustment
        $start = \Carbon\Carbon::parse($leave->start_date);
        $end = \Carbon\Carbon::parse($leave->end_date);
        $days = $leave->is_half_day ? 0.5 : ($start->diffInDays($end) + 1);

        $allocation = \App\Models\LeaveAllocation::where('user_id', $leave->user_id)
            ->where('leave_type', $leave->leave_type)
            ->where('year', \Carbon\Carbon::parse($leave->start_date)->year)
            ->first();

        // 1. If transitioning TO approved, deduct from balance
        if ($request->status === 'approved' && $oldStatus !== 'approved') {
            if ($allocation) {
                // Safeguard: Prevent negative balance if needed (optional but recommended)
                $allocation->increment('used_days', $days);
            }
        }

        // 2. If transitioning FROM approved to anything else, restore balance
        if ($oldStatus === 'approved' && $request->status !== 'approved') {
            if ($allocation) {
                $allocation->decrement('used_days', $days);
            }
        }

        // Notify Employee
        \App\Helpers\NotificationHelper::send(
            $leave->user_id,
            'Leave Request ' . ucfirst($request->status),
            "Your leave request from {$leave->start_date} has been " . $request->status . ($request->admin_comment ? ": " . $request->admin_comment : "."),
            $request->status === 'approved' ? 'success' : 'danger',
            'leaves'
        );

        return redirect()->back()->with('success', 'Leave status updated and employee notified.');
    }
}
