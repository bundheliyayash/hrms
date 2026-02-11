<x-admin-layout>
    <x-slot name="header">
        Worker Replacement Logs
    </x-slot>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">Staff Substitution Management</h5>
                        <p class="text-muted small mb-0">Track and manage worker replacements due to absenteeism or emergencies.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.replacements.pending') }}" class="btn btn-warning btn-sm position-relative">
                            <i class="bi bi-person-exclamation"></i> Pending Requests
                            @php $pendingCount = \App\Models\WorkerReplacement::where('status', 'pending')->count(); @endphp
                            @if($pendingCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $pendingCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('admin.replacements.daily-report') }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-file-earmark-bar-graph"></i> Substitution Report
                        </a>
                        <a href="{{ route('admin.replacements.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-person-fill-gear"></i> New Substitution
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <form action="{{ route('admin.replacements.index') }}" method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}" placeholder="Filter by Date">
                </div>
                <div class="col-md-3">
                    <select name="reason" class="form-select form-select-sm">
                        <option value="">All Reasons</option>
                        <option value="absent" {{ request('reason') == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="leave" {{ request('reason') == 'leave' ? 'selected' : '' }}>Leave</option>
                        <option value="emergency" {{ request('reason') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                        <option value="client_request" {{ request('reason') == 'client_request' ? 'selected' : '' }}>Client Request</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bi bi-filter"></i> Search Logs</button>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Original Worker</th>
                            <th></th>
                            <th>Replacement</th>
                            <th>Site / Reason</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($replacements as $replacement)
                        <tr>
                            <td class="ps-3 fw-bold">{{ $replacement->originalAssignment->assigned_date->format('d M, Y') }}</td>
                            <td>
                                <div class="fw-bold text-danger">{{ $replacement->originalWorker->name }}</div>
                                <small class="text-muted">Emp ID: {{ $replacement->originalWorker->employeeDetail->employee_id ?? 'N/A' }}</small>
                            </td>
                            <td class="text-center px-0">
                                <i class="bi bi-arrow-right text-muted"></i>
                            </td>
                            <td>
                                <div class="fw-bold text-success">{{ $replacement->replacementWorker->name }}</div>
                                <small class="text-muted">Emp ID: {{ $replacement->replacementWorker->employeeDetail->employee_id ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-primary">{{ $replacement->originalAssignment->site->site_name }}</div>
                                <span class="badge bg-light text-dark border small">{{ ucfirst($replacement->reason) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $replacement->status == 'approved' ? 'bg-success' : ($replacement->status == 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                    {{ strtoupper($replacement->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.replacements.show', $replacement) }}" class="btn btn-sm btn-outline-info" title="View Details"><i class="bi bi-eye"></i></a>
                                @if($replacement->status == 'pending')
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">Decision</button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li>
                                            <form action="{{ route('admin.replacements.approve', $replacement) }}" method="POST">
                                                @csrf<button type="submit" class="dropdown-item text-success"><i class="bi bi-check-circle me-2"></i> Approve</button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.replacements.reject', $replacement) }}" method="POST">
                                                @csrf<button type="submit" class="dropdown-item text-danger"><i class="bi bi-x-circle me-2"></i> Reject</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                                <form action="{{ route('admin.replacements.destroy', $replacement) }}" method="POST" class="d-inline" data-confirm-delete="true" data-delete-message="Delete replacement request?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-archive"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No replacement records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $replacements->links() }}
        </div>
    </div>
</x-admin-layout>
