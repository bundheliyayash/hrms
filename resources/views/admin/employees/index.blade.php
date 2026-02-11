<x-admin-layout>
    <x-slot name="header">
        Employee Management
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-muted">Employee List</h5>
            <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus"></i> Add New Employee
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Name / Email</th>
                            <th>Emp ID</th>
                            <th>Role</th>
                            <th>Dept / Designation</th>
                            <th>Status</th>
                            <th class="text-end px-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        <tr>
                            <td class="px-3">
                                <div class="fw-bold">{{ $employee->name }}</div>
                                <div class="text-muted small">{{ $employee->email }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $employee->employeeDetail->employee_id ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">{{ ucfirst($employee->role) }}</span>
                            </td>
                            <td>
                                <div>{{ $employee->employeeDetail->department ?? '-' }}</div>
                                <small class="text-muted">{{ $employee->employeeDetail->designation ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $employee->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($employee->status) }}
                                </span>
                            </td>
                            <td class="text-end px-3">
                                <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" class="d-inline" data-confirm-delete="true" data-delete-message="Archive {{ $employee->name }}? They will no longer be able to log in.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Archive">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $employees->links() }}
        </div>
    </div>
</x-admin-layout>
