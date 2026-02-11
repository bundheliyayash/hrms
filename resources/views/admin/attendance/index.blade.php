<x-admin-layout>
    <x-slot name="header">
        Attendance Logs
    </x-slot>

    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 mb-1 text-gray-800 fw-bold">Attendance Central</h2>
                <p class="text-muted small mb-0">Manage daily logs and professional timesheets.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.attendance.export', ['date' => $date]) }}" class="btn btn-success rounded-pill px-4">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
                </a>
                <a href="{{ route('admin.attendance.requests') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="bi bi-bell"></i> Correction Requests
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif

        <!-- Tabs Nav -->
        <ul class="nav nav-pills mb-4 bg-white p-2 rounded-pill shadow-sm d-inline-flex" id="attendanceTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 fw-bold" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily" type="button">Daily Logs</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 fw-bold" id="employees-tab" data-bs-toggle="tab" data-bs-target="#employees" type="button">Client-Wise (Employees)</button>
            </li>
        </ul>

        <div class="tab-content" id="attendanceTabsContent">
            <!-- Daily Logs Tab -->
            <div class="tab-pane fade show active" id="daily" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Daily Logs for {{ \Carbon\Carbon::parse($date)->format('d M, Y') }}</h6>
                        <form action="{{ route('admin.attendance.index') }}" method="GET">
                            <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="px-4 py-3">Employee</th>
                                        <th class="py-3">Client / Site</th>
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
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 35px; height: 35px;">
                                                    {{ substr($att->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $att->user->name }}</div>
                                                    <div class="small text-muted">{{ $att->user->employeeDetail->employee_id ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold small">{{ $att->site->client->name ?? 'N/A' }}</div>
                                            <div class="text-muted small">{{ $att->site->site_name ?? 'N/A' }}</div>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($att->clock_in)->format('h:i A') }}</td>
                                        <td>{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : 'Active' }}</td>
                                        <td>{{ floor($att->duration_minutes / 60) }}h {{ $att->duration_minutes % 60 }}m</td>
                                        <td class="text-center">
                                            <span class="badge {{ $att->status == 'present' ? 'bg-success' : 'bg-warning text-dark' }} border-0 px-3 rounded-pill">
                                                {{ strtoupper($att->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end px-4">
                                            <a href="{{ route('admin.attendance.index', ['user_id' => $att->user_id]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">Professional View</a>
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
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="mb-0 fw-bold">Select Employee to View Timesheet</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="px-4 py-3">Employee Name</th>
                                        <th class="py-3">Designation</th>
                                        <th class="py-3">Assigned Client</th>
                                        <th class="py-3">Current Assignment</th>
                                        <th class="py-3 text-end px-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employees as $emp)
                                    <tr>
                                        <td class="px-4">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 35px; height: 35px;">
                                                    {{ substr($emp->name, 0, 1) }}
                                                </div>
                                                <div class="fw-bold">{{ $emp->name }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $emp->employeeDetail->designation ?? 'N/A' }}</td>
                                        <td class="fw-bold text-primary">{{ $emp->employeeDetail->site->client->name ?? 'N/A' }}</td>
                                        <td>{{ $emp->employeeDetail->site->site_name ?? 'N/A' }}</td>
                                        <td class="text-end px-4">
                                            <a href="{{ route('admin.attendance.index', ['user_id' => $emp->id]) }}" class="btn btn-primary btn-sm rounded-pill px-4">View Timesheet</a>
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
</x-admin-layout>
