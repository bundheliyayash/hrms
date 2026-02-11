<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\User;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $holidayService;

    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    public function index()
    {
        return view('admin.reports.index');
    }

    public function attendance()
    {
        $attendances = Attendance::with('user')->orderBy('date', 'desc')->paginate(15);
        return view('admin.reports.attendance', compact('attendances'));
    }

    public function leaves()
    {
        // Summary counts
        $totalLeaves = Leave::count();
        $approvedLeaves = Leave::where('status', 'approved')->count();
        $pendingLeaves = Leave::where('status', 'pending')->count();
        $rejectedLeaves = Leave::where('status', 'rejected')->count();

        $leaves = Leave::with('user')->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.reports.leaves', compact('leaves', 'totalLeaves', 'approvedLeaves', 'pendingLeaves', 'rejectedLeaves'));
    }

    public function payroll()
    {
        $totalPayrollPaid = Payroll::where('status', 'paid')->sum('net_salary');
        $payrolls = Payroll::with('user')->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.reports.payroll', compact('payrolls', 'totalPayrollPaid'));
    }

    public function musterRoll(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;
        
        $employees = User::where('role', 'employee')->with(['attendances' => function($q) use ($month, $year) {
            $q->whereMonth('date', $month)->whereYear('date', $year);
        }])->get();

        $holidays = \App\Models\Holiday::where('status', 'active')
            ->where(function ($q) use ($month, $year) {
                $q->whereMonth('start_date', $month)->whereYear('start_date', $year)
                  ->orWhereMonth('end_date', $month)->whereYear('end_date', $year);
            })->get();

        return view('admin.reports.muster-roll', compact('employees', 'daysInMonth', 'month', 'year', 'holidays'));
    }

    public function wageRegister(Request $request) 
    {
        $month = $request->get('month', date('F')); // Default current month name
        $year = $request->get('year', date('Y'));

        $payrolls = Payroll::with('user.employeeDetail')
                           ->where('month', $month)
                           ->where('year', $year)
                           ->get();
                           
        return view('admin.reports.wage-register', compact('payrolls', 'month', 'year'));
    }
}
