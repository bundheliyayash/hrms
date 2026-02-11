<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\ClientSite;
use App\Models\EmployeeDetail;
use App\Models\HolidayDepartment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $type = $request->get('type');
        
        $query = Holiday::with(['departments', 'sites'])
            ->whereYear('start_date', $year);

        if ($type) {
            $query->where('type', $type);
        }

        $holidays = $query->orderBy('start_date', 'asc')->get();
        $years = Holiday::selectRaw('YEAR(start_date) as year')->distinct()->orderBy('year', 'desc')->pluck('year');
        if ($years->isEmpty()) {
            $years = collect([date('Y')]);
        }

        return view('admin.holidays.index', compact('holidays', 'years', 'year', 'type'));
    }

    public function calendar(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $holidays = Holiday::where('status', 'active')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate]);
            })->get();

        return view('admin.holidays.calendar', compact('holidays', 'month', 'year'));
    }

    public function create()
    {
        $departments = EmployeeDetail::whereNotNull('department')->distinct()->pluck('department');
        $sites = ClientSite::all();
        return view('admin.holidays.create', compact('departments', 'sites'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'required|in:paid,unpaid,optional',
            'applicable_type' => 'required|in:all,department,site',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
            'departments' => 'required_if:applicable_type,department|array',
            'sites' => 'required_if:applicable_type,site|array',
        ]);

        DB::transaction(function () use ($validated) {
            $holiday = Holiday::create([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? $validated['start_date'],
                'type' => $validated['type'],
                'applicable_type' => $validated['applicable_type'],
                'status' => $validated['status'],
                'description' => $validated['description'],
            ]);

            if ($validated['applicable_type'] === 'department') {
                foreach ($validated['departments'] as $dept) {
                    HolidayDepartment::create([
                        'holiday_id' => $holiday->id,
                        'department' => $dept,
                    ]);
                }
            } elseif ($validated['applicable_type'] === 'site') {
                $holiday->sites()->sync($validated['sites']);
            }
        });

        return redirect()->route('admin.holidays.index')->with('success', 'Holiday created successfully.');
    }

    public function edit(Holiday $holiday)
    {
        $departments = EmployeeDetail::whereNotNull('department')->distinct()->pluck('department');
        $sites = ClientSite::all();
        $holiday->load(['departments', 'sites']);
        return view('admin.holidays.edit', compact('holiday', 'departments', 'sites'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'required|in:paid,unpaid,optional',
            'applicable_type' => 'required|in:all,department,site',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
            'departments' => 'required_if:applicable_type,department|array',
            'sites' => 'required_if:applicable_type,site|array',
        ]);

        DB::transaction(function () use ($validated, $holiday) {
            $holiday->update([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? $validated['start_date'],
                'type' => $validated['type'],
                'applicable_type' => $validated['applicable_type'],
                'status' => $validated['status'],
                'description' => $validated['description'],
            ]);

            // Clear existing relations
            $holiday->departments()->delete();
            $holiday->sites()->detach();

            if ($validated['applicable_type'] === 'department') {
                foreach ($validated['departments'] as $dept) {
                    HolidayDepartment::create([
                        'holiday_id' => $holiday->id,
                        'department' => $dept,
                    ]);
                }
            } elseif ($validated['applicable_type'] === 'site') {
                $holiday->sites()->sync($validated['sites']);
            }
        });

        return redirect()->route('admin.holidays.index')->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->route('admin.holidays.index')->with('success', 'Holiday deleted successfully.');
    }
}
