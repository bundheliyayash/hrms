<x-admin-layout>
    <x-slot name="header">
        Daily Worker Assignments
    </x-slot>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body bg-light rounded d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-primary">Deployment Overview</h5>
                        <p class="text-muted small mb-0">Manage daily worker movement and site deployments.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.assignments.calendar') }}" class="btn btn-outline-primary">
                            <i class="bi bi-calendar3"></i> Calendar View
                        </a>
                        <a href="{{ route('admin.assignments.deployment-sheet') }}" class="btn btn-success">
                            <i class="bi bi-printer"></i> Deployment Sheet
                        </a>
                        <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary">
                            <i class="bi bi-person-plus-fill"></i> New Assignment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <form action="{{ route('admin.assignments.index') }}" method="GET" class="row g-2">
                <div class="col-md-3">
                    <label class="small text-muted mb-1">Date</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date', date('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="small text-muted mb-1">Site</label>
                    <select name="site_id" class="form-select form-select-sm">
                        <option value="">All Sites</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>{{ $site->site_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small text-muted mb-1">Employee</label>
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 pt-4">
                    <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bi bi-filter"></i> Apply Filters</button>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Employee</th>
                            <th>Target Site</th>
                            <th>Shift</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold">{{ $assignment->assigned_date->format('d M, Y') }}</div>
                                <small class="text-muted">{{ $assignment->assigned_date->format('l') }}</small>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $assignment->employee->name }}</div>
                                <small class="text-muted">ID: {{ $assignment->employee->employeeDetail->employee_id ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-success">{{ $assignment->site->site_name }}</div>
                                <small class="text-muted">{{ $assignment->site->location }}</small>
                            </td>
                            <td>
                                @if($assignment->shift)
                                    <span class="badge bg-light text-dark border">{{ $assignment->shift->name }} ({{ $assignment->shift->start_time->format('H:i') }} - {{ $assignment->shift->end_time->format('H:i') }})</span>
                                @else
                                    <span class="text-muted small">Standard Shift</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary rounded-pill small">{{ ucfirst($assignment->assignment_type) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $assignment->status == 'completed' ? 'bg-success' : ($assignment->status == 'assigned' ? 'bg-primary' : ($assignment->status == 'cancelled' ? 'bg-danger' : 'bg-warning')) }}">
                                    {{ ucfirst($assignment->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                        Options
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#reassignModal{{ $assignment->id }}"><i class="bi bi-arrow-repeat me-2 text-warning"></i> Reassign Site</a></li>
                                        @if($assignment->status !== 'cancelled' && $assignment->status !== 'completed')
                                        <li>
                                            <form action="{{ route('admin.assignments.destroy', $assignment->id) }}" method="POST" data-confirm-delete="true" data-delete-title="Delete Assignment?" data-delete-message="This will remove the employee from the site schedule.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100 text-start">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.replacements.create', ['assignment_id' => $assignment->id]) }}"><i class="bi bi-person-exclamation me-2 text-info"></i> Request Substitute</a></li>
                                    </ul>
                                </div>

                                <!-- Reassign Modal -->
                                <div class="modal fade" id="reassignModal{{ $assignment->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content text-start">
                                            <form action="{{ route('admin.assignments.reassign', $assignment) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reassign Worker: {{ $assignment->employee->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Current Site:</label>
                                                        <div class="form-control bg-light">{{ $assignment->site->site_name }}</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">New Target Site <span class="text-danger">*</span></label>
                                                        <select name="site_id" class="form-select" required>
                                                            <option value="">Select New Site...</option>
                                                            @foreach($sites as $site)
                                                                @if($site->id != $assignment->site_id)
                                                                    <option value="{{ $site->id }}">{{ $site->site_name }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Reason for Reassignment <span class="text-danger">*</span></label>
                                                        <textarea name="reason" class="form-control" rows="2" required placeholder="e.g. Client requested emergency cleanup..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-warning">Execute Reassignment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x display-4"></i>
                                <p class="mt-2">No worker assignments found for the selected criteria.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $assignments->links() }}
        </div>
    </div>
</x-admin-layout>
