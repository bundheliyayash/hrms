<x-admin-layout>
    <x-slot name="header">
        Attendance Logs
    </x-slot>

    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h2 class="h3 mb-1 text-dark fw-bold">Attendance Central</h2>
                <p class="text-muted small mb-0 fw-medium">Real-time daily monitoring and professional timesheet management.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.attendance.requests') }}" class="btn btn-outline-primary rounded-pill px-4 shadow-sm fw-bold">
                    <i class="bi bi-bell-fill me-1"></i> Corrections
                </a>
                <a href="{{ route('admin.attendance.export', ['date' => $date]) }}" class="btn btn-success rounded-pill px-4 shadow-sm text-white fw-bold">
                    <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Export Data
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3">{{ session('success') }}</div>
        @endif

        <!-- Tabs Nav -->
        <div class="mb-4">
            <ul class="nav nav-pills bg-white p-2 rounded-pill shadow-sm d-inline-flex border" id="attendanceTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 fw-bold" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily" type="button">Daily Master Log</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-bold" id="employees-tab" data-bs-toggle="tab" data-bs-target="#employees" type="button">Staff Directory View</button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="attendanceTabsContent">
            <!-- Daily Logs Tab -->
            <div class="tab-pane fade show active" id="daily" role="tabpanel">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-calendar-check me-2 text-primary"></i>Logs for {{ \Carbon\Carbon::parse($date)->format('d M, Y') }}</h6>
                            <a href="{{ route('admin.reports.attendance', ['month' => date('m', strtotime($date)), 'year' => date('Y', strtotime($date))]) }}" class="btn btn-sm btn-soft-info rounded-pill px-3 fw-bold">
                                <i class="bi bi-calendar3-range me-1"></i> Monthly View
                            </a>
                        </div>
                        <form action="{{ route('admin.attendance.index') }}" method="GET" class="d-flex align-items-center gap-2">
                            <span class="small text-muted fw-bold uppercase fs-xs">Change Date:</span>
                            <input type="date" name="date" class="form-control form-control-sm border-light-subtle" value="{{ $date }}" onchange="this.form.submit()">
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="px-4 py-3">Employee</th>
                                        <th class="py-3">Deployment</th>
                                        <th class="py-3">Clock In</th>
                                        <th class="py-3">Clock Out</th>
                                        <th class="py-3">Duration</th>
                                        <th class="py-3 text-center">Status</th>
                                        <th class="py-3 text-end px-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($attendances as $att)
                                    <tr>
                                        <td class="px-4">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold border border-primary-subtle" style="width: 38px; height: 38px;">
                                                    {{ substr($att->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $att->user->name }}</div>
                                                    <div class="small text-muted fw-medium">{{ $att->user->employeeDetail->employee_id ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold small text-dark">{{ $att->site->client->name ?? 'N/A' }}</div>
                                            <div class="text-muted small fw-medium"><i class="bi bi-geo-alt me-1 text-primary"></i> {{ $att->site->site_name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="fw-bold text-dark">{{ \Carbon\Carbon::parse($att->clock_in)->format('h:i A') }}</td>
                                        <td class="fw-bold text-dark">{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '<span class="text-success blink small fw-bold">ON DUTY</span>' }}</td>
                                        <td>
                                            <span class="fw-medium">{{ floor($att->duration_minutes / 60) }}h {{ $att->duration_minutes % 60 }}m</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $att->status == 'present' ? 'bg-success' : 'bg-warning text-dark' }} border-0 px-3 rounded-pill small">
                                                {{ strtoupper($att->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end px-4">
                                            <a href="{{ route('admin.attendance.index', ['user_id' => $att->user_id]) }}" class="btn btn-light btn-sm rounded-pill px-3 fw-bold border">
                                                <i class="bi bi-eye me-1"></i> Timesheet
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted">No attendance found for this date.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client-Wise Employees Tab -->
            <div class="tab-pane fade" id="employees" role="tabpanel">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="mb-0 fw-bold text-dark">Employee Professional Directory</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="px-4 py-3">Employee Name</th>
                                        <th class="py-3">Designation</th>
                                        <th class="py-3">Primary Client</th>
                                        <th class="py-3">Active Site</th>
                                        <th class="py-3 text-end px-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employees as $emp)
                                    <tr>
                                        <td class="px-4">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light-subtle text-secondary rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold border" style="width: 38px; height: 38px;">
                                                    {{ substr($emp->name, 0, 1) }}
                                                </div>
                                                <div class="fw-bold text-dark">{{ $emp->name }}</div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border fw-medium">{{ $emp->employeeDetail->designation ?? 'Staff Member' }}</span></td>
                                        <td class="fw-bold text-primary">{{ $emp->employeeDetail->site->client->name ?? 'N/A' }}</td>
                                        <td class="small fw-medium text-muted"><i class="bi bi-pin-map me-1 text-danger"></i> {{ $emp->employeeDetail->site->site_name ?? 'N/A' }}</td>
                                        <td class="text-end px-4">
                                            <a href="{{ route('admin.attendance.index', ['user_id' => $emp->id]) }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm fw-bold">View History</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table thead th { font-weight: 600; font-size: 0.7rem; border-bottom: none; }
        .table tbody td { border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; }
        .btn-soft-info { background-color: #e0f2fe; color: #0369a1; }
        .btn-soft-info:hover { background-color: #bae6fd; }
        .bg-primary-subtle { background-color: #e0f2fe !important; }
        .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
        .fs-xs { font-size: 0.65rem; }
        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
        .blink { animation: blink 1.5s infinite; }
    </style>
</x-admin-layout>
