<x-admin-layout>
    <div class="d-md-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Management Portal</h4>
            <p class="text-muted small mb-0">Monitor your team's performance and site operations.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary rounded-pill px-4 btn-sm fw-600 shadow-sm">
                <i class="bi bi-person-plus me-1"></i>Assign Staff
            </a>
        </div>
    </div>

    <style>
        .premium-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; transition: all 0.2s; overflow: hidden; }
        .premium-card:hover { transform: translateY(-3px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.05); }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .table thead th { background: #f8fafc; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; padding: 1rem; }
        .table tbody td { padding: 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .avatar-box { width: 36px; height: 36px; border-radius: 50%; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; }
        .fw-600 { font-weight: 600; }
    </style>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="premium-card p-3 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-600 mb-1">TEAM STRENGTH</div>
                        <h2 class="fw-bold mb-0">{{ $stats['total_staff'] }}</h2>
                        <div class="small text-muted mt-2">Active employees under you</div>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary shadow-sm">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="premium-card p-3 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-600 mb-1">PRESENT TODAY</div>
                        <h2 class="fw-bold mb-0 text-success">{{ $stats['present_today'] }}</h2>
                        <div class="small text-muted mt-2">Attendance rate: {{ $stats['total_staff'] > 0 ? round(($stats['present_today']/$stats['total_staff'])*100) : 0 }}%</div>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success shadow-sm">
                        <i class="bi bi-check-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="premium-card p-3 shadow-sm">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-600 mb-1">PENDING REPLACEMENTS</div>
                        <h2 class="fw-bold mb-0 text-danger">{{ $stats['pending_replacements'] }}</h2>
                        <div class="small text-muted mt-2">Critical staffing gaps to fill</div>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger shadow-sm">
                        <i class="bi bi-person-x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Attendance Tracking -->
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="premium-card">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Live Team Attendance</h6>
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-link btn-sm text-primary text-decoration-none fw-bold">View Timesheet</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Employee</th>
                                <th>Assigned Site</th>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendance as $att)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-box me-3">
                                            {{ strtoupper(substr($att->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold small">{{ $att->user->name }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">ID: {{ $att->user->employeeDetail->employee_id ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small fw-600"><i class="bi bi-building me-1"></i>{{ $att->site->site_name ?? 'Head Office' }}</span>
                                </td>
                                <td>
                                    <div class="small">In: {{ \Carbon\Carbon::parse($att->clock_in)->format('h:i A') }}</div>
                                    <div class="small text-muted">Out: {{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '--:--' }}</div>
                                </td>
                                <td>
                                    @php
                                        $color = match($att->status) {
                                            'present' => 'success',
                                            'late' => 'warning',
                                            'absent' => 'danger',
                                            default => 'info'
                                        };
                                    @endphp
                                    <span class="badge rounded-pill bg-{{ $color }}-subtle text-{{ $color }} px-3" style="font-size: 0.65rem;">
                                        {{ strtoupper($att->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-light border rounded-pill"><i class="bi bi-three-dots"></i></button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                                    <div class="small fw-600">No attendance activity tracked yet.</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Toolbox -->
        <div class="col-xl-4">
            <div class="premium-card mb-4 bg-dark text-white p-4">
                <h6 class="fw-bold mb-4">Operations Toolbox</h6>
                <div class="d-grid gap-3">
                    <a href="{{ route('admin.attendance.requests') }}" class="btn btn-primary d-flex align-items-center justify-content-between p-3 rounded-4 shadow-sm border-0">
                        <div class="text-start">
                            <div class="fw-bold mb-0">Verify Requests</div>
                            <div class="small opacity-75" style="font-size: 0.7rem;">Manage staff corrections</div>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('admin.assignments.index') }}" class="btn btn-light d-flex align-items-center justify-content-between p-3 rounded-4 border-0">
                        <div class="text-start">
                            <div class="fw-bold mb-0 text-dark">Roster View</div>
                            <div class="small text-muted" style="font-size: 0.7rem;">Daily staff deployment</div>
                        </div>
                        <i class="bi bi-calendar3 text-dark"></i>
                    </a>
                </div>
            </div>

            <div class="premium-card p-4">
                <h6 class="fw-bold mb-3">Management Tip</h6>
                <div class="p-3 bg-light border-0 rounded-4">
                    <p class="small text-muted mb-0">Regularly verify location mismatches to ensure staff are at their assigned sites and reduce unauthorized punctures.</p>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
