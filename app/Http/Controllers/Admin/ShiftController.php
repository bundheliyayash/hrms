<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::latest()->paginate(10);
        return view('admin.shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('admin.shifts.create_edit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'clock_in_time' => 'required',
            'clock_out_time' => 'required',
            'late_threshold_minutes' => 'required|integer|min:0',
            'early_out_threshold_minutes' => 'required|integer|min:0',
            'break_duration_minutes' => 'required|integer|min:0',
            'is_active' => 'boolean'
        ]);

        Shift::create($validated);

        return redirect()->route('admin.shifts.index')->with('success', 'Shift created successfully.');
    }

    public function edit(Shift $shift)
    {
        return view('admin.shifts.create_edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'clock_in_time' => 'required',
            'clock_out_time' => 'required',
            'late_threshold_minutes' => 'required|integer|min:0',
            'early_out_threshold_minutes' => 'required|integer|min:0',
            'break_duration_minutes' => 'required|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $shift->update($validated);

        return redirect()->route('admin.shifts.index')->with('success', 'Shift updated successfully.');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return redirect()->route('admin.shifts.index')->with('success', 'Shift deleted successfully.');
    }
}
