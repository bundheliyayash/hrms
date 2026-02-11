<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyAssignment;
use App\Models\ClientSite;
use App\Models\Contract;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DailyAssignment::with(['employee', 'site', 'shift', 'contract']);

        if ($request->filled('date')) {
            $query->whereDate('assigned_date', $request->date);
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        $assignments = $query->latest('assigned_date')->paginate(20);
        $sites = ClientSite::where('is_active', true)->orderBy('site_name')->get();
        $employees = User::where('role', 'employee')->where('status', 'active')->orderBy('name')->get();

        return view('admin.assignments.index', compact('assignments', 'sites', 'employees'));
    }

    /**
     * Calendar view for assignments.
     */
    public function calendar(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $assignments = DailyAssignment::with(['employee', 'site'])
            ->whereBetween('assigned_date', [$start, $end])
            ->get()
            ->groupBy(function($item) {
                return $item->assigned_date->format('Y-m-d');
            });

        return view('admin.assignments.calendar', compact('assignments', 'start', 'end'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sites = ClientSite::where('is_active', true)->orderBy('site_name')->get();
        $employees = User::where('role', 'employee')->where('status', 'active')->orderBy('name')->get();
        $shifts = Shift::where('is_active', true)->get();
        
        return view('admin.assignments.create', compact('sites', 'employees', 'shifts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'site_id' => 'required|exists:client_sites,id',
            'assigned_date' => 'required|date',
            'shift_id' => 'nullable|exists:shifts,id',
            'notes' => 'nullable|string',
        ]);

        // Check for conflicts
        if (DailyAssignment::hasConflict($validated['user_id'], $validated['assigned_date'])) {
            return back()->with('error', 'Employee is already assigned to another site on this date.')
                ->withInput();
        }

        // Find active contract for this site
        $contract = Contract::active()
            ->whereHas('sites', fn($q) => $q->where('client_sites.id', $validated['site_id']))
            ->first();

        if (!$contract) {
            return back()->with('error', 'No active contract found for this site.')->withInput();
        }

        DailyAssignment::create([
            ...$validated,
            'contract_id' => $contract->id,
            'assignment_type' => 'temporary', // Default manual assignment
            'assigned_by' => auth()->id(),
            'status' => 'assigned',
        ]);

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Worker assigned successfully.');
    }

    /**
     * Bulk assign workers.
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'site_id' => 'required|exists:client_sites,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'shift_id' => 'nullable|exists:shifts,id',
            'days' => 'nullable|array', // Mon, Tue, etc.
        ]);

        $contract = Contract::active()
            ->whereHas('sites', fn($q) => $q->where('client_sites.id', $validated['site_id']))
            ->first();

        if (!$contract) {
            return back()->with('error', 'No active contract found for this site.');
        }

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $days = $request->input('days', ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']);
        
        $assignedCount = 0;
        $conflictCount = 0;

        DB::beginTransaction();
        try {
            foreach ($validated['user_ids'] as $userId) {
                $current = $start->copy();
                
                while ($current->lte($end)) {
                    // Check if day is selected
                    if (in_array($current->format('D'), $days)) {
                        $dateString = $current->format('Y-m-d');
                        
                        // Check for conflicts
                        if (!DailyAssignment::hasConflict($userId, $dateString)) {
                            DailyAssignment::create([
                                'user_id' => $userId,
                                'site_id' => $validated['site_id'],
                                'contract_id' => $contract->id,
                                'assigned_date' => $dateString,
                                'shift_id' => $validated['shift_id'],
                                'assignment_type' => 'temporary',
                                'assigned_by' => auth()->id(),
                                'status' => 'assigned',
                            ]);
                            $assignedCount++;
                        } else {
                            $conflictCount++;
                        }
                    }
                    $current->addDay();
                }
            }
            DB::commit();

            return back()->with('success', "Assigned {$assignedCount} shifts. {$conflictCount} skipped due to conflicts.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk assignment failed: ' . $e->getMessage());
        }
    }

    /**
     * Reassign a worker to a different site.
     */
    public function reassign(Request $request, DailyAssignment $assignment)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:client_sites,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'reason' => 'required|string',
        ]);

        if (!$assignment->canBeCancelled()) {
            return back()->with('error', 'Cannot reassign completed assignments.');
        }

        // Find active contract for new site
        $contract = Contract::active()
            ->whereHas('sites', fn($q) => $q->where('client_sites.id', $validated['site_id']))
            ->first();

        if (!$contract) {
            return back()->with('error', 'No active contract found for the new site.');
        }

        DB::beginTransaction();
        try {
            // Cancel current assignment
            $assignment->update([
                'status' => 'cancelled',
                'notes' => $assignment->notes . "\nReassigned: " . $validated['reason'],
            ]);

            // Create new assignment
            DailyAssignment::create([
                'user_id' => $assignment->user_id,
                'site_id' => $validated['site_id'],
                'contract_id' => $contract->id,
                'assigned_date' => $assignment->assigned_date,
                'shift_id' => $validated['shift_id'] ?? $assignment->shift_id,
                'assignment_type' => 'temporary',
                'assigned_by' => auth()->id(),
                'status' => 'assigned',
                'notes' => "Reassigned from previous site. Reason: " . $validated['reason'],
            ]);

            DB::commit();
            return back()->with('success', 'Worker reassigned successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Reassignment failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate deployment sheet for printing.
     */
    public function deploymentSheet(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        
        $assignments = DailyAssignment::with(['employee', 'site', 'shift'])
            ->whereDate('assigned_date', $date)
            ->whereIn('status', ['assigned', 'confirmed'])
            ->get()
            ->groupBy('site.site_name');

        return view('admin.assignments.deployment-sheet', compact('assignments', 'date'));
    }

    /**
     * Cancel an assignment.
     */
    public function destroy(DailyAssignment $assignment)
    {
        if (!$assignment->canBeCancelled()) {
            return back()->with('error', 'Cannot cancel completed assignments or assignments with attendance.');
        }

        $assignment->update(['status' => 'cancelled']);
        
        return back()->with('success', 'Assignment cancelled successfully.');
    }
}
