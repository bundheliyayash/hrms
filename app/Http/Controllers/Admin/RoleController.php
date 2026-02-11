<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_rbac');
        $roles = Role::withCount('users')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        Gate::authorize('manage_rbac');
        $permissions = Permission::all()->groupBy('category');
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_rbac');
        $validated = $request->validate([
            'name' => 'required|unique:roles,name',
            'display_name' => 'required',
            'description' => 'nullable|string',
            'permissions' => 'required|array'
        ]);

        $role = Role::create($validated);
        $role->permissions()->sync($request->permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        Gate::authorize('manage_rbac');
        $permissions = Permission::all()->groupBy('category');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        Gate::authorize('manage_rbac');
        $validated = $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'display_name' => 'required',
            'description' => 'nullable|string',
            'permissions' => 'required|array'
        ]);

        $role->update($validated);
        $role->permissions()->sync($request->permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        Gate::authorize('manage_rbac');
        if ($role->name === 'admin') {
            return back()->with('error', 'Cannot delete admin role.');
        }
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }
}
