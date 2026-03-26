<x-admin-layout>
    <x-slot name="header">Salary Advances</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0 fw-bold">Salary Advance Records</h5>
            <p class="text-muted small mb-0">Track all advances given to employees and their payroll recovery status.</p>
        </div>
        <a href="{{ route('admin.salary-advances.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-circle me-1"></i> Record Advance
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Employee</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                        <option value="approved"  {{ request('status') === 'approved'  ? 'selected' : '' }}>Approved</option>
                        <option value="rejected"  {{ request('status') === 'rejected'  ? 'selected' : '' }}>Rejected</option>
                        <option value="recovered" {{ request('status') === 'recovered' ? 'selected' : '' }}>Recovered</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary btn-sm px-4">Filter</button>
                    <a href="{{ route('admin.salary-advances.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3">Employee</th>
                        <th class="py-3">Amount</th>
                        <th class="py-3">Advance Date</th>
                        <th class="py-3">Recovery Month</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Recovery</th>
                        <th class="py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advances as $adv)
                    <tr>
                        <td class="px-4">
                            <div class="fw-bold">{{ $adv->user->name }}</div>
                            <div class="small text-muted">{{ $adv->user->employeeDetail->employee_id ?? '-' }}</div>
                        </td>
                        <td class="fw-bold">₹{{ number_format($adv->amount, 2) }}</td>
                        <td>{{ $adv->advance_date->format('d M Y') }}</td>
                        <td>
                            @if($adv->recovery_month)
                                {{ $adv->recovery_month->format('M Y') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badge = match($adv->status) {
                                    'approved'  => 'success',
                                    'rejected'  => 'danger',
                                    'recovered' => 'secondary',
                                    default     => 'warning',
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }} border border-{{ $badge }}-subtle rounded-pill px-3">
                                {{ ucfirst($adv->status) }}
                            </span>
                        </td>
                        <td>
                            @if($adv->status === 'approved')
                                <div class="small">
                                    <span class="text-success">₹{{ number_format($adv->recovered_amount, 2) }}</span>
                                    <span class="text-muted"> / ₹{{ number_format($adv->amount, 2) }}</span>
                                </div>
                                @if($adv->pending_amount > 0)
                                    <div class="small text-danger">Pending: ₹{{ number_format($adv->pending_amount, 2) }}</div>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($adv->status === 'pending')
                                    <form action="{{ route('admin.salary-advances.approve', $adv) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.salary-advances.reject', $adv) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">Reject</button>
                                    </form>
                                @endif
                                @if(in_array($adv->status, ['pending', 'approved']) && $adv->recovered_amount == 0)
                                    <form action="{{ route('admin.salary-advances.destroy', $adv) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this advance record?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-2">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($adv->admin_remark)
                                    <span class="text-muted small ms-1" title="{{ $adv->admin_remark }}" data-bs-toggle="tooltip">
                                        <i class="bi bi-chat-left-text"></i>
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-cash-coin fs-2 d-block mb-2"></i>
                            No advance records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($advances->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $advances->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
