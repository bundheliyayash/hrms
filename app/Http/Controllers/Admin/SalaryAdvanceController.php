<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalaryAdvance;
use App\Models\User;
use Illuminate\Http\Request;

class SalaryAdvanceController extends Controller
{
    public function index(Request $request)
    {
        $query = SalaryAdvance::with(['user', 'approvedBy'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $advances  = $query->paginate(20)->withQueryString();
        $employees = User::whereIn('role', ['employee', 'manager'])->orderBy('name')->get();

        return view('admin.salary-advances.index', compact('advances', 'employees'));
    }

    public function create()
    {
        $employees = User::whereIn('role', ['employee', 'manager'])->orderBy('name')->get();
        return view('admin.salary-advances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'amount'        => 'required|numeric|min:1',
            'advance_date'  => 'required|date',
            'recovery_month'=> 'required|date',
            'reason'        => 'nullable|string|max:500',
        ]);

        SalaryAdvance::create([
            'user_id'        => $validated['user_id'],
            'amount'         => $validated['amount'],
            'advance_date'   => $validated['advance_date'],
            'recovery_month' => $validated['recovery_month'] . '-01',
            'reason'         => $validated['reason'] ?? null,
            'status'         => 'approved',
            'approved_by'    => auth()->id(),
        ]);

        \App\Helpers\NotificationHelper::send(
            $validated['user_id'],
            'Salary Advance Recorded',
            '₹' . number_format($validated['amount'], 2) . ' advance has been recorded for ' . \Carbon\Carbon::parse($validated['recovery_month'] . '-01')->format('F Y') . ' payroll.',
            'info',
            'payroll'
        );

        return redirect()->route('admin.salary-advances.index')->with('success', 'Salary advance recorded and employee notified.');
    }

    public function approve(Request $request, SalaryAdvance $salaryAdvance)
    {
        $request->validate(['admin_remark' => 'nullable|string|max:500']);

        $salaryAdvance->update([
            'status'       => 'approved',
            'approved_by'  => auth()->id(),
            'admin_remark' => $request->admin_remark,
        ]);

        \App\Helpers\NotificationHelper::send(
            $salaryAdvance->user_id,
            'Salary Advance Approved',
            '₹' . number_format($salaryAdvance->amount, 2) . ' advance request has been approved.',
            'success',
            'payroll'
        );

        return redirect()->back()->with('success', 'Advance approved.');
    }

    public function reject(Request $request, SalaryAdvance $salaryAdvance)
    {
        $request->validate(['admin_remark' => 'nullable|string|max:500']);

        $salaryAdvance->update([
            'status'       => 'rejected',
            'approved_by'  => auth()->id(),
            'admin_remark' => $request->admin_remark,
        ]);

        \App\Helpers\NotificationHelper::send(
            $salaryAdvance->user_id,
            'Salary Advance Rejected',
            'Your advance request of ₹' . number_format($salaryAdvance->amount, 2) . ' has been rejected.' . ($request->admin_remark ? ' Reason: ' . $request->admin_remark : ''),
            'danger',
            'payroll'
        );

        return redirect()->back()->with('success', 'Advance rejected.');
    }

    public function destroy(SalaryAdvance $salaryAdvance)
    {
        if ($salaryAdvance->status === 'recovered') {
            return redirect()->back()->with('error', 'Cannot delete a fully recovered advance.');
        }
        $salaryAdvance->delete();
        return redirect()->route('admin.salary-advances.index')->with('success', 'Advance record deleted.');
    }
}
