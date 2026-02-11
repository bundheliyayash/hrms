<x-admin-layout>
    <x-slot name="header">
        Holiday Management
    </x-slot>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <form action="{{ route('admin.holidays.index') }}" method="GET" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0 fw-bold small text-muted">Year:</label>
                            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach($years as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-0 fw-bold small text-muted">Type:</label>
                            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <option value="paid" {{ $type == 'paid' ? 'selected' : '' }}>Paid Holiday</option>
                                <option value="unpaid" {{ $type == 'unpaid' ? 'selected' : '' }}>Unpaid Holiday</option>
                                <option value="optional" {{ $type == 'optional' ? 'selected' : '' }}>Optional</option>
                            </select>
                        </div>
                        <div class="col-auto ms-auto d-flex gap-2">
                            <a href="{{ route('admin.holidays.calendar') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-calendar3 me-1"></i> Calendar View
                            </a>
                            <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Add Holiday
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="mb-0 fw-bold">Holidays in {{ $year }}</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Holiday Name</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Applicability</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holidays as $holiday)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $holiday->name }}</div>
                                @if($holiday->description)
                                    <small class="text-muted">{{ $holiday->description }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-medium">
                                    {{ $holiday->start_date->format('d M') }}
                                    @if($holiday->end_date && $holiday->end_date->gt($holiday->start_date))
                                        - {{ $holiday->end_date->format('d M') }}
                                    @endif
                                </span>
                                <div class="small text-muted">{{ $holiday->start_date->format('Y') }}</div>
                            </td>
                            <td>
                                @if($holiday->type === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2">Paid</span>
                                @elseif($holiday->type === 'unpaid')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">Unpaid</span>
                                @else
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2">Optional</span>
                                @endif
                            </td>
                            <td>
                                @if($holiday->applicable_type === 'all')
                                    <span class="text-muted small">All Employees</span>
                                @elseif($holiday->applicable_type === 'department')
                                    <span class="text-primary small">
                                        {{ $holiday->departments->count() }} Departments
                                    </span>
                                @else
                                    <span class="text-info small">
                                        {{ $holiday->sites->count() }} Sites
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($holiday->status === 'active')
                                    <span class="badge bg-success border-0 px-2">Active</span>
                                @else
                                    <span class="badge bg-secondary border-0 px-2">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.holidays.edit', $holiday->id) }}" class="btn btn-sm btn-outline-primary border-0">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.holidays.destroy', $holiday->id) }}" method="POST" class="d-inline" data-confirm-delete="true" data-delete-message="Delete holiday {{ $holiday->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x display-6 mb-3 d-block"></i>
                                No holidays found for selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
