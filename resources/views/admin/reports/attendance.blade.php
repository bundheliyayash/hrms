<x-admin-layout>
    <x-slot name="header">
        Attendance Report
    </x-slot>

    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="mb-0 fw-bold text-dark">Monthly Attendance Report</h3>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row g-4 align-items-end">
                <div class="col-lg-9">
                    <form action="{{ route('admin.reports.attendance') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted uppercase">Month</label>
                            <select name="month" class="form-select border-light-subtle">
                                @foreach(range(1, 12) as $m)
                                    @php $mVal = sprintf('%02d', $m); @endphp
                                    <option value="{{ $mVal }}" {{ ($month ?? date('m')) == $mVal ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-muted uppercase">Year</label>
                            <select name="year" class="form-select border-light-subtle">
                                @foreach(range(date('Y')-2, date('Y')+1) as $y)
                                    <option value="{{ $y }}" {{ ($year ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted uppercase">Employee Filter</label>
                            <select name="user_id" class="form-select border-light-subtle">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ ($userId ?? '') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->employeeDetail->employee_id ?? 'No ID' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label invisible d-block">Filter</label>
                            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                                <i class="bi bi-search me-1"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-3 border-start">
                    <form action="{{ route('admin.reports.attendance.excel') }}" method="GET">
                        <input type="hidden" name="month" value="{{ $month ?? date('m') }}">
                        <input type="hidden" name="year" value="{{ $year ?? date('Y') }}">
                        <input type="hidden" name="user_id" value="{{ $userId ?? '' }}">
                        <label class="form-label small fw-bold text-muted uppercase">Export Data</label>
                        <button type="submit" class="btn btn-outline-success w-100 fw-bold border-2">
                            <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i>Export to Excel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-muted">Attendance Master Sheet</h6>
            <span class="badge bg-light text-dark border small">{{ $attendances->total() }} Records Found</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="py-3">Employee</th>
                             <th class="py-3">Shift Logs</th>
                            <th class="py-3">Total Time</th>
                            <th class="py-3">Verification</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end px-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $record)
                        @php $isVirtual = !$record->id; @endphp
                        <tr class="{{ $isVirtual ? 'bg-light-subtle opacity-75' : '' }}">
                            <td class="px-4 fw-bold text-secondary">{{ \Carbon\Carbon::parse($record->date)->format('d M, Y') }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $record->user->name }}</div>
                                <div class="small text-muted">{{ $record->user->employeeDetail->employee_id ?? 'N/A' }}</div>
                            </td>
                            <td>
                                @if(!$isVirtual)
                                <div class="d-flex align-items-center gap-2">
                                    <span class="small bg-success-subtle text-success px-2 py-1 rounded">In: {{ \Carbon\Carbon::parse($record->clock_in)->format('h:i A') }}</span>
                                    <span class="small bg-light text-muted px-2 py-1 rounded">Out: {{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('h:i A') : 'Active' }}</span>
                                </div>
                                @else
                                <span class="text-muted small">---</span>
                                @endif
                            </td>
                            <td>
                                @if($record->duration_minutes)
                                    <span class="fw-bold">{{ floor($record->duration_minutes/60) }}h {{ $record->duration_minutes%60 }}m</span>
                                @else
                                    <span class="text-muted small">--</span>
                                @endif
                            </td>
                            <td>
                                @if(!$isVirtual)
                                    @if($record->is_verified)
                                        <span class="text-info small fw-bold" title="{{ $record->distance_detected }}m away">
                                            <i class="bi bi-geo-alt-fill me-1"></i> GPS Verified
                                        </span>
                                    @else
                                        <span class="text-muted small"><i class="bi bi-shield-lock me-1"></i> Manual</span>
                                    @endif
                                @else
                                <span class="text-muted small">---</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClass = 'bg-danger';
                                    if ($record->status == 'present') $statusClass = 'bg-success';
                                    elseif ($record->status == 'late') $statusClass = 'bg-warning text-dark';
                                    elseif ($record->status == 'holiday') $statusClass = 'bg-info text-dark';
                                @endphp
                                <span class="badge {{ $statusClass }} border-0 px-3 rounded-pill small">
                                    {{ strtoupper($record->status) }}
                                </span>
                            </td>
                            <td class="text-end px-4">
                                <a href="{{ route('admin.attendance.index', ['user_id' => $record->user_id]) }}" class="btn btn-light btn-sm rounded-circle" title="View Timesheet">
                                    <i class="bi bi-arrow-right-short fs-5"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No attendance found for this selection.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $attendances->links() }}
        </div>
    </div>

    <style>
        .table thead th { font-weight: 600; font-size: 0.7rem; border-bottom: none; }
        .table tbody td { border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; }
        .bg-success-subtle { background-color: #f0fdf4 !important; }
        .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</x-admin-layout>
