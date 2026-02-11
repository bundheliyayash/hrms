<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDetail;
use App\Models\User;
use App\Models\ClientSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::where('role', '!=', 'admin')->with('employeeDetail')->paginate(10);
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $sites = ClientSite::where('is_active', true)
            ->whereHas('client', function($q) {
                $q->where('is_active', true);
            })->get()->filter(fn($site) => $site->client->isServiceActive());

        $shifts = \App\Models\Shift::where('is_active', true)->get();
        $roles = \App\Models\Role::all();
            
        return view('admin.employees.create', compact('sites', 'shifts', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'employee_id' => 'required|string|unique:employee_details',
            'phone' => 'nullable|string',
            'department' => 'nullable|string',
            'designation' => 'nullable|string',
            'joining_date' => 'nullable|date',
            'basic_salary' => 'nullable|numeric',
            'site_id' => 'nullable|exists:client_sites,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'role_id' => 'required|exists:roles,id',
            'employment_type' => 'nullable|string|in:On-roll,Temporary',
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'aadhar_number' => 'nullable|string',
            'pan_number' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => \App\Models\Role::find($validated['role_id'])->name,
                'role_id' => $validated['role_id'],
            ]);

            EmployeeDetail::create([
                'user_id' => $user->id,
                'employee_id' => $validated['employee_id'],
                'phone' => $validated['phone'] ?? null,
                'department' => $validated['department'] ?? null,
                'designation' => $validated['designation'] ?? null,
                'joining_date' => $validated['joining_date'] ?? null,
                'basic_salary' => $validated['basic_salary'] ?? 0,
                'site_id' => $validated['site_id'] ?? null,
                'shift_id' => $validated['shift_id'] ?? null,
                'employment_type' => $request->employment_type ?? 'On-roll',
                'bank_name' => $validated['bank_name'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'ifsc_code' => $validated['ifsc_code'] ?? null,
                'aadhar_number' => $validated['aadhar_number'] ?? null,
                'pan_number' => $validated['pan_number'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit($id)
    {
        $employee = User::with('employeeDetail')->findOrFail($id);
        $sites = ClientSite::where('is_active', true)
            ->whereHas('client', function($q) {
                $q->where('is_active', true);
            })->get()->filter(fn($site) => $site->client->isServiceActive());

        $shifts = \App\Models\Shift::where('is_active', true)->get();
        $roles = \App\Models\Role::all();
            
        return view('admin.employees.edit', compact('employee', 'sites', 'shifts', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $employee = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string',
            'department' => 'nullable|string',
            'designation' => 'nullable|string',
            'basic_salary' => 'nullable|numeric',
            'site_id' => 'nullable|exists:client_sites,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'role_id' => 'required|exists:roles,id',
            'employment_type' => 'nullable|string|in:On-roll,Temporary',
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'aadhar_number' => 'nullable|string',
            'pan_number' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
        ]);

        $employee->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => \App\Models\Role::find($validated['role_id'])->name,
            'role_id' => $validated['role_id'],
        ]);

        $employee->employeeDetail()->updateOrCreate(
            ['user_id' => $employee->id],
            [
                'phone' => $validated['phone'] ?? null,
                'department' => $validated['department'] ?? null,
                'designation' => $validated['designation'] ?? null,
                'basic_salary' => $validated['basic_salary'] ?? 0,
                'site_id' => $validated['site_id'] ?? null,
                'shift_id' => $validated['shift_id'] ?? null,
                'employment_type' => $request->employment_type ?? 'On-roll',
                'bank_name' => $validated['bank_name'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'ifsc_code' => $validated['ifsc_code'] ?? null,
                'aadhar_number' => $validated['aadhar_number'] ?? null,
                'pan_number' => $validated['pan_number'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            ]
        );

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function toggleStatus(User $employee)
    {
        $employee->update(['status' => $employee->status === 'active' ? 'inactive' : 'active']);
        $status = $employee->status === 'active' ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Employee {$status} successfully.");
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->route('admin.employees.index')->with('success', 'Employee archived successfully.');
    }
}
