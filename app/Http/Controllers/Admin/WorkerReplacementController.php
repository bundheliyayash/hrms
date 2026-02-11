<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkerReplacement;
use App\Models\DailyAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkerReplacementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WorkerReplacement::with(['originalWorker', 'replacementWorker', 'site', 'requestedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('replacement_date', $request->date);
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        $replacements = $query->latest()->paginate(15);

        return view('admin.replacements.index', compact('replacements'));
    }

    /**
     * Display pending replacements.
     */
    public function pending()
    {
        $replacements = WorkerReplacement::pending()
            ->with(['originalWorker', 'replacementWorker', 'site', 'requestedBy'])
            ->latest()
            ->paginate(15);
            
        return view('admin.replacements.pending', compact('replacements'));
    }

    /**
     * Show the form for requesting a replacement.
     */
    public function create(Request $request)
    {
        $assignment = null;
        if ($request->filled('assignment_id')) {
            $assignment = DailyAssignment::with(['employee', 'site', 'shift'])->findOrFail($request->assignment_id);
        }

        $availableWorkers = User::where('role', 'employee')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.replacements.create', compact('assignment', 'availableWorkers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'original_assignment_id' => 'required|exists:daily_assignments,id',
            'replacement_worker_id' => 'required|exists:users,id|different:original_worker_id',
            'reason' => 'required|in:absent,leave,emergency,client_request,other',
            'reason_details' => 'nullable|string',
        ]);

        $assignment = DailyAssignment::findOrFail($validated['original_assignment_id']);

        if (!$assignment->canBeCancelled()) {
            return back()->with('error', 'Cannot request replacement for completed or past assignments.');
        }

        // Check availability
        if (DailyAssignment::hasConflict($validated['replacement_worker_id'], $assignment->assigned_date->format('Y-m-d'))) {
            return back()->with('error', 'Replacement worker is already assigned for this date.');
        }

        WorkerReplacement::create([
            'original_assignment_id' => $assignment->id,
            'original_worker_id' => $assignment->user_id,
            'replacement_worker_id' => $validated['replacement_worker_id'],
            'site_id' => $assignment->site_id,
            'replacement_date' => $assignment->assigned_date,
            'shift_id' => $assignment->shift_id,
            'reason' => $validated['reason'],
            'reason_details' => $validated['reason_details'],
            'requested_by' => auth()->id(),
            'status' => 'pending',
        ]);

        return redirect()->route('admin.replacements.pending')
            ->with('success', 'Replacement request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkerReplacement $replacement)
    {
        $replacement->load(['originalWorker', 'replacementWorker', 'site', 'originalAssignment']);
        return view('admin.replacements.show', compact('replacement'));
    }

    /**
     * Approve the replacement request.
     */
    public function approve(WorkerReplacement $replacement)
    {
        if (!$replacement->isPending()) {
            return back()->with('error', 'Request is not pending.');
        }

        if ($replacement->approve(auth()->id())) {
            return back()->with('success', 'Replacement approved and assignments updated.');
        }

        return back()->with('error', 'Failed to approve replacement. Identify conflict or system error.');
    }

    /**
     * Reject the replacement request.
     */
    public function reject(WorkerReplacement $replacement)
    {
        if (!$replacement->isPending()) {
            return back()->with('error', 'Request is not pending.');
        }

        $replacement->reject(auth()->id());
        
        return back()->with('success', 'Replacement request rejected.');
    }

    /**
     * Daily substitution report.
     */
    public function dailyReport(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        
        $replacements = WorkerReplacement::approved()
            ->forDate($date)
            ->with(['originalWorker', 'replacementWorker', 'site'])
            ->get();

        return view('admin.replacements.daily-report', compact('replacements', 'date'));
    }
}
