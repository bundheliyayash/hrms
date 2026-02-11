<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    protected $holidayService;

    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    public function index()
    {
        $payrolls = Payroll::with('user')->latest()->paginate(10);
        return view('admin.payroll.index', compact('payrolls'));
    }

    public function create()
    {
        $employees = User::where('role', 'employee')->get();
        return view('admin.payroll.create', compact('employees'));
    }

    // New Helper to fetch data via AJAX or internal use
    public function fetchStats(Request $request, \App\Services\PayrollService $payrollService)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month_year' => 'required', // Format YYYY-MM
        ]);

        $userId = $request->user_id;
        $date = Carbon::createFromFormat('Y-m', $request->month_year);
        $user = User::findOrFail($userId);

        $stats = $payrollService->calculateSalary($user, $date->month, $date->year);
        
        return response()->json($stats);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|string', // Month Name
            'year' => 'required|integer',
            'basic_salary' => 'required|numeric',
            'allowances' => 'nullable|numeric',
            'deductions' => 'nullable|numeric',
            
            // Calculated fields coming from form
            'total_days' => 'required|integer',
            'working_days' => 'nullable|numeric', // We can store payable days here or separate
            'present_days' => 'required|numeric',
            'payable_days' => 'required|numeric',
            'per_day_salary' => 'required|numeric',
            'ot_hours' => 'nullable|numeric',
            'ot_amount' => 'nullable|numeric',
            'pf_amount' => 'nullable|numeric',
            'esi_amount' => 'nullable|numeric',
            'net_salary' => 'required|numeric',
            'paid_holidays' => 'nullable|numeric',
        ]);

        Payroll::create([
            'user_id' => $validated['user_id'],
            'month' => $validated['month'], // Ensure format (e.g. 'January')
            'year' => $validated['year'],
            'basic_salary' => $validated['basic_salary'],
            'allowances' => $validated['allowances'] ?? 0,
            'deductions' => $validated['deductions'] ?? 0,
            
            'total_days' => $validated['total_days'],
            'present_days' => $validated['present_days'],
            'payable_days' => $validated['payable_days'],
            'paid_holidays' => $validated['paid_holidays'] ?? 0,
            'per_day_salary' => $validated['per_day_salary'],
            'ot_hours' => $validated['ot_hours'] ?? 0,
            'ot_amount' => $validated['ot_amount'] ?? 0,
            'pf_amount' => $validated['pf_amount'] ?? 0,
            'esi_amount' => $validated['esi_amount'] ?? 0,
            
            'net_salary' => $validated['net_salary'],
            'status' => 'paid',
        ]);

        return redirect()->route('admin.payroll.index')->with('success', 'Payroll generated successfully.');
    }

    public function show(Payroll $payroll)
    {
        return view('admin.payroll.show', compact('payroll'));
    }
}
