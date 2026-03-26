<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\SalaryAdvance;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'month_year' => 'required|regex:/^\d{4}-\d{2}$/', // Format YYYY-MM
            ]);

            $userId = $request->user_id;
            $date = Carbon::createFromFormat('Y-m', $request->month_year);
            $user = User::findOrFail($userId);

            $stats = $payrollService->calculateSalary($user, $date->month, $date->year);

            // Auto-fill pending advance for this recovery month
            $recoveryMonthStart = $date->copy()->startOfMonth()->format('Y-m-d');
            $pendingAdvance = \App\Models\SalaryAdvance::where('user_id', $userId)
                ->where('status', 'approved')
                ->whereDate('recovery_month', $recoveryMonthStart)
                ->where('recovered_amount', '<', \Illuminate\Support\Facades\DB::raw('amount'))
                ->sum(\Illuminate\Support\Facades\DB::raw('amount - recovered_amount'));

            $stats['advance_default'] = round($pendingAdvance, 2);

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unable to fetch payroll stats: ' . $e->getMessage()
            ], 422);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'month' => 'required|string', // Month Name
                'year' => 'required|integer',
                'basic_salary' => 'required|numeric',
                'allowances' => 'nullable|numeric',
                'deductions' => 'nullable|numeric',
                'gross_salary' => 'nullable|numeric',
                'hra' => 'nullable|numeric',
                'washing_allowance' => 'nullable|numeric',
                
                // Calculated fields coming from form
                'total_days' => 'required|integer',
                'working_days' => 'nullable|numeric',
                'present_days' => 'required|numeric',
                'payable_days' => 'required|numeric',
                'per_day_salary' => 'required|numeric',
                'ot_hours' => 'nullable|numeric',
                'ot_amount' => 'nullable|numeric',
                'pf_amount' => 'nullable|numeric',
                'esi_amount' => 'nullable|numeric',
                'pt_amount' => 'nullable|numeric',
                'advance_amount' => 'nullable|numeric',
                'net_salary' => 'required|numeric',
                'bank_amount' => 'required|numeric|min:0',
                'cash_amount' => 'required|numeric|min:0',
                'paid_holidays' => 'nullable|numeric',
            ]);

            DB::transaction(function () use ($validated) {
                $payroll = Payroll::create([
                    'user_id' => $validated['user_id'],
                    'month' => $validated['month'],
                    'year' => $validated['year'],
                    'basic_salary' => $validated['basic_salary'],
                    'allowances' => $validated['allowances'] ?? 0,
                    'deductions' => $validated['deductions'] ?? 0,
                    'gross_salary' => $validated['gross_salary'] ?? 0,
                    'hra' => $validated['hra'] ?? 0,
                    'washing_allowance' => $validated['washing_allowance'] ?? 0,
                    
                    'total_days' => $validated['total_days'],
                    'present_days' => $validated['present_days'],
                    'payable_days' => $validated['payable_days'],
                    'paid_holidays' => $validated['paid_holidays'] ?? 0,
                    'per_day_salary' => $validated['per_day_salary'],
                    'ot_hours' => $validated['ot_hours'] ?? 0,
                    'ot_amount' => $validated['ot_amount'] ?? 0,
                    'pf_amount' => $validated['pf_amount'] ?? 0,
                    'esi_amount' => $validated['esi_amount'] ?? 0,
                    'pt_amount' => $validated['pt_amount'] ?? 0,
                    'advance_amount' => $validated['advance_amount'] ?? 0,
                    
                    'net_salary' => $validated['net_salary'],
                    'bank_amount' => $validated['bank_amount'],
                    'cash_amount' => $validated['cash_amount'],
                    'status' => 'paid',
                ]);

                // Hard Lock: Once Payroll is 'paid', lock all attendance and leaves for that period
                $carbonDate = Carbon::createFromFormat('M Y', $validated['month'] . ' ' . $validated['year']);
                $startDate = $carbonDate->copy()->startOfMonth()->format('Y-m-d');
                $endDate = $carbonDate->copy()->endOfMonth()->format('Y-m-d');

                Attendance::where('user_id', $validated['user_id'])
                    ->where('date', '>=', $startDate)
                    ->where('date', '<=', $endDate)
                    ->update(['is_locked' => true]);

                Leave::where('user_id', $validated['user_id'])
                    ->where('status', 'approved')
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                              ->orWhereBetween('end_date', [$startDate, $endDate]);
                    })
                    ->update(['is_locked' => true]);

                // Mark salary advances as recovered for this month
                $advanceDeducted = $validated['advance_amount'] ?? 0;
                if ($advanceDeducted > 0) {
                    $pendingAdvances = SalaryAdvance::where('user_id', $validated['user_id'])
                        ->where('status', 'approved')
                        ->whereDate('recovery_month', $startDate)
                        ->whereColumn('recovered_amount', '<', 'amount')
                        ->get();

                    $remaining = $advanceDeducted;
                    foreach ($pendingAdvances as $adv) {
                        if ($remaining <= 0) break;
                        $toRecover = min($adv->pending_amount, $remaining);
                        $newRecovered = $adv->recovered_amount + $toRecover;
                        $newStatus = $newRecovered >= $adv->amount ? 'recovered' : 'approved';
                        $adv->update([
                            'recovered_amount' => $newRecovered,
                            'status'           => $newStatus,
                            'payroll_id'       => $payroll->id,
                        ]);
                        $remaining -= $toRecover;
                    }
                }
            });

            return redirect()->route('admin.payroll.index')->with('success', 'Payroll generated successfully for ' . $validated['month'] . ' ' . $validated['year']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Critical Error: ' . $e->getMessage());
        }
    }

    public function show(Payroll $payroll)
    {
        return view('admin.payroll.show', compact('payroll'));
    }
}
