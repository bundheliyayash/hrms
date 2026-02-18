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

    public function attendance(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        $userId = $request->get('user_id');

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        if ($userId) {
            // SINGLE EMPLOYEE VIEW: Fill Gaps
            $targetUser = User::withTrashed()->findOrFail($userId);
            $attendancesRaw = Attendance::where('user_id', $userId)
                ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->with(['user' => function($q) { $q->withTrashed(); }])
                ->get();

            $attendanceMap = $attendancesRaw->keyBy('date');
            $allDates = [];
            $current = $start->copy();
            
            while ($current <= $end) {
                $dateStr = $current->format('Y-m-d');
                if ($attendanceMap->has($dateStr)) {
                    $allDates[] = $attendanceMap->get($dateStr);
                } else {
                    $status = ($current->isSunday() || $this->holidayService->isHoliday($targetUser, $dateStr)) ? 'holiday' : 'absent';
                    $virtual = new Attendance(['user_id' => $userId, 'date' => $dateStr, 'status' => $status]);
                    $virtual->setRelation('user', $targetUser);
                    $allDates[] = $virtual;
                }
                $current->addDay();
            }

            // Convert to Collection and Paginate manually if needed, or just return all for the month
            // Given it's max 31 rows, we can just return all or simple pagination
            $attendances = collect($allDates)->sortByDesc('date');
            // For consistency with Blade Link pagination, we can use a LengthAwarePaginator if we want, 
            // but for 31 rows, simple is fine. Let's use simple for now. 
            // Wait, the blade view uses $attendances->links().
            $perPage = 31; 
            $attendances = new \Illuminate\Pagination\LengthAwarePaginator(
                $attendances->forPage($request->get('page', 1), $perPage),
                $attendances->count(),
                $perPage,
                $request->get('page', 1),
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            // MASTER SHEET: Standard Paginated List
            $attendances = Attendance::with(['user' => function($q) { $q->withTrashed(); }])
                ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->orderBy('date', 'desc')
                ->paginate(20)
                ->withQueryString();
        }
        
        $employees = User::withTrashed()->where('role', 'employee')->orderBy('name')->get();

        return view('admin.reports.attendance', compact('attendances', 'month', 'year', 'employees', 'userId'));
    }

    public function leaves()
    {
        // Summary counts
        $totalLeaves = Leave::count();
        $approvedLeaves = Leave::where('status', 'approved')->count();
        $pendingLeaves = Leave::where('status', 'pending')->count();
        $rejectedLeaves = Leave::where('status', 'rejected')->count();

        $leaves = Leave::with(['user' => function($q) { $q->withTrashed(); }])->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.reports.leaves', compact('leaves', 'totalLeaves', 'approvedLeaves', 'pendingLeaves', 'rejectedLeaves'));
    }

    public function payroll()
    {
        abort_if(!auth()->user()->isAdmin(), 403);
        $totalPayrollPaid = Payroll::where('status', 'paid')->sum('net_salary');
        $payrolls = Payroll::with(['user' => function($q) { $q->withTrashed(); }])->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.reports.payroll', compact('payrolls', 'totalPayrollPaid'));
    }

    public function musterRoll(Request $request)
    {
        abort_if(!auth()->user()->isAdmin(), 403);
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        $daysInMonth = Carbon::createFromDate($year, $month)->daysInMonth;
        
        $employees = User::withTrashed()->where('role', 'employee')->with(['attendances' => function($q) use ($month, $year) {
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
        abort_if(!auth()->user()->isAdmin(), 403);
        $month = $request->get('month', date('F')); // Default current month name
        $year = $request->get('year', date('Y'));

        $payrolls = Payroll::with(['user' => fn($q) => $q->withTrashed(), 'user.employeeDetail'])
                           ->where('month', $month)
                           ->where('year', $year)
                           ->get();
                           
        return view('admin.reports.wage-register', compact('payrolls', 'month', 'year'));
    }
}
