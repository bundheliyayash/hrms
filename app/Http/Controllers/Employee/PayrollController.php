<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    public function index()
    {
        $payrolls = Payroll::where('user_id', Auth::id())->orderBy('created_at', 'desc')->paginate(10);
        return view('employee.payroll.index', compact('payrolls'));
    }

    public function show($id)
    {
        $payroll = Payroll::where('user_id', Auth::id())->with('user.employeeDetail')->findOrFail($id);
        return view('admin.payroll.show', compact('payroll')); // Reuse the same payslip view
    }
}
