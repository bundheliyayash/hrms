<x-admin-layout>
    <div class="d-md-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">Dashboard</h4>
            <p class="text-muted small mb-0">Welcome back! Here's what's happening today.</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button class="btn btn-white border rounded-pill px-4 btn-sm fw-600">Override Attendance</button>
            <button class="btn btn-primary rounded-pill px-4 btn-sm fw-600">
                <i class="bi bi-plus-lg me-1"></i> Add Employee
            </button>
        </div>
    </div>

    <style>
        .stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem;
            height: 100%;
            transition: all 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .stat-label { font-size: 0.75rem; color: #64748b; font-weight: 500; margin-bottom: 0.5rem; }
        .stat-value { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; display: flex; align-items: baseline; }
        .stat-subtext { font-size: 0.7rem; color: #94a3b8; }
        .stat-badge { font-size: 0.65rem; padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 8px; font-weight: 600; }
        .badge-up { background: #ecfdf5; color: #10b981; }
        .badge-down { background: #fef2f2; color: #ef4444; }

        .quick-action-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .quick-action-card:hover { border-color: #3b82f6; background-color: #eff6ff; }
        .quick-action-card i { font-size: 1.5rem; margin-bottom: 0.75rem; display: block; }
        .quick-action-card.primary { background: #3b82f6; color: #fff; border-color: #3b82f6; }
        .quick-action-card.primary:hover { opacity: 0.9; background: #3b82f6; }
        .quick-action-card.primary .text-muted { color: rgba(255,255,255,0.7) !important; }

        .chart-container { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }

        .data-table-container { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
        .table thead th { background: #f8fafc; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; padding: 1rem; border-bottom: 1px solid #e2e8f0; }
        .table tbody td { padding: 1rem; font-size: 0.8125rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }

        .alert-item { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; margin-bottom: 0.75rem; border-left-width: 4px; }
        .site-item { border-bottom: 1px solid #f1f5f9; padding: 0.5rem 0; cursor: pointer; transition: background 0.15s; }
        .site-item:last-child { border-bottom: none; }
        .site-item:hover { background-color: #f8fafc; }
        .sites-list { max-height: 280px; overflow-y: auto; }
        .status-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
        .fw-600 { font-weight: 600; }
    </style>

    <!-- Stats Cards Row 1 -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="stat-label">Total Employees</div>
                        <div class="stat-value">
                            {{ $stats['total_employees'] }}
                        </div>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <div class="stat-subtext">Active: <b>{{ $stats['active_employees'] }}</b></div>
                    <div class="stat-subtext">Total: <b>{{ $stats['total_employees'] }}</b></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="stat-label">Today's Attendance</div>
                        <div class="stat-value">
                            {{ $stats['attendance_rate'] }}%
                        </div>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="stat-subtext mb-2">{{ $stats['present_count'] }} out of {{ $stats['active_employees'] }} active employees present</div>
                <div class="d-flex gap-2">
                    <div class="stat-subtext">Present: <b>{{ $stats['present_count'] }}</b></div>
                    <div class="stat-subtext">Absent: <b>{{ $stats['absent_count'] }}</b></div>
                    <div class="stat-subtext">Late: <b>{{ $stats['late_count'] }}</b></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="stat-label">Pending Requests</div>
                        <div class="stat-value">
                            {{ $stats['pending_requests'] }}
                        </div>
                    </div>
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                </div>
                <div class="stat-subtext">Leave and correction requests pending</div>
            </div>
        </div>
        
        <!-- Row 2 -->
        <div class="col-md-4 col-lg-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="stat-label">Active Clients</div>
                        <div class="stat-value">{{ $stats['active_clients'] }}</div>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
                <div class="stat-subtext mb-2">Managing across all sites</div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="stat-label">This Month's Payroll</div>
                        <div class="stat-value">₹{{ $stats['monthly_payroll'] }}</div>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                </div>
                <div class="stat-subtext">Total processed this month</div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="stat-label">Location Alerts</div>
                        <div class="stat-value">
                            {{ $stats['location_alerts'] }}
                        </div>
                    </div>
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                </div>
                <div class="stat-subtext">Suspicious punches detected today</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h6 class="fw-bold mb-3">Quick Actions</h6>
    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <a href="{{ route('admin.employees.create') }}" class="text-decoration-none">
                <div class="quick-action-card primary shadow-sm">
                    <i class="bi bi-person-plus"></i>
                    <div class="fw-bold small">Add Employee</div>
                    <div class="text-muted" style="font-size: 0.65rem;">Register new staff</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.attendance.requests') }}" class="text-decoration-none">
                <div class="quick-action-card shadow-sm text-dark">
                    <i class="bi bi-calendar-check text-primary"></i>
                    <div class="fw-bold small">Override Attendance</div>
                    <div class="text-muted small" style="font-size: 0.65rem;">Manual correction</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.reports.index') }}" class="text-decoration-none">
                <div class="quick-action-card shadow-sm text-dark">
                    <i class="bi bi-file-earmark-pdf text-primary"></i>
                    <div class="fw-bold small">Generate Report</div>
                    <div class="text-muted small" style="font-size: 0.65rem;">Export data</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.activity-logs.index') }}" class="text-decoration-none">
                <div class="quick-action-card shadow-sm text-dark">
                    <i class="bi bi-shield-exclamation text-primary"></i>
                    <div class="fw-bold small">View Alerts</div>
                    <div class="text-muted small" style="font-size: 0.65rem;">View system logs</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row gx-4">
        <div class="col-lg-8">
            <div class="chart-container shadow-sm">
                <div class="chart-header">
                    <div>
                        <h6 class="fw-bold mb-0">
                            Weekly Attendance Trend 
                            <i class="bi bi-info-circle ms-1 text-primary" style="cursor: help;" 
                               data-bs-toggle="tooltip" data-bs-placement="top" 
                               title="Analyze daily attendance patterns. Peaks indicate full attendance, while dips represent leaves or absences. Overlap shows late-in trends."></i>
                        </h6>
                        <p class="text-muted small mb-0">Employee attendance for the current week</p>
                    </div>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-container shadow-sm h-100 mb-0">
                <div class="chart-header">
                    <div>
                        <h6 class="fw-bold mb-0">Employee Distribution</h6>
                        <p class="text-muted small mb-0">Employees assigned per client</p>
                    </div>
                </div>
                <div style="height: 220px; position: relative;">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <!-- Today's Attendance Table -->
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0">Today's Attendance</h6>
            </div>
            <div class="data-table-container shadow-sm">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Client</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentAttendance as $att)
                        <tr>
                            <td class="fw-bold">{{ $att->user->employeeDetail->employee_id ?? 'N/A' }}</td>
                            <td class="fw-bold">{{ $att->user->name }}</td>
                            <td class="text-muted">{{ $att->site->client->name ?? 'N/A' }}</td>
                            <td>{{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('H:i') : '-' }}</td>
                            <td>{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('H:i') : '-' }}</td>
                            <td>
                                @php 
                                    $statusLabel = ucfirst(str_replace('_', ' ', $att->status));
                                    $sColor = match($statusLabel) {
                                        'Present' => '#ecfdf5; color: #10b981',
                                        'Late' => '#fff7ed; color: #f97316',
                                        'Absent' => '#fef2f2; color: #ef4444',
                                        'Half day' => '#eff6ff; color: #3b82f6',
                                        default => '#f1f5f9; color: #64748b'
                                    };
                                @endphp
                                <span class="badge py-1 px-3 border-0" style="background: {{ $sColor }}; font-size: 0.7rem;">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                @php $isVerified = $att->is_verified; @endphp
                                <span class="text-{{ $isVerified ? 'success' : 'danger' }} small">
                                    <i class="bi bi-{{ $isVerified ? 'check-circle' : 'exclamation-circle' }}-fill me-1"></i>
                                    {{ $isVerified ? 'Verified' : 'Suspicious' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-link p-0 text-info"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-link p-0 text-primary"><i class="bi bi-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-3 bg-white border-top d-flex justify-content-center">
                    {{ $recentAttendance->links() }}
                </div>
            </div>
        </div>

        <!-- Alerts Sidebar -->
        <div class="col-lg-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-shield-exclamation text-warning me-2"></i>Active Alerts</h6>
                <a href="#" class="text-primary small fw-bold">View All</a>
            </div>
            @foreach($alerts as $alert)
            <div class="alert-item shadow-sm border-{{ $alert['color'] }}">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold small text-{{ $alert['color'] }}">{{ $alert['type'] }}</span>
                    <span class="text-muted" style="font-size: 0.65rem;">{{ $alert['time'] }}</span>
                </div>
                <div class="text-dark fw-bold small">{{ $alert['user'] }}</div>
                <div class="text-muted" style="font-size: 0.75rem;">{{ $alert['msg'] }}</div>
            </div>
            @endforeach

            <!-- Site Overview Mini Card -->
            <div class="chart-container shadow-sm mt-4 p-0">
                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 small">Client Sites</h6>
                    <div class="d-flex gap-2" style="font-size: 0.65rem;">
                        <span class="text-muted"><span class="status-dot me-1" style="background: #10b981;"></span>Active</span>
                        <span class="text-muted"><span class="status-dot me-1" style="background: #cbd5e1;"></span>Inactive</span>
                    </div>
                </div>
                <div class="px-3 py-2 sites-list">
                    @foreach($sites as $site)
                    <div class="site-item d-flex align-items-center">
                        <span class="status-dot me-2" style="background: {{ ($site['is_active'] ?? true) ? '#10b981' : '#cbd5e1' }};"></span>
                        <i class="bi bi-building text-muted me-2" style="font-size: 0.75rem;"></i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate" style="font-size: 0.8rem;">{{ $site['name'] }}</div>
                            <div class="text-muted text-truncate" style="font-size: 0.65rem;">{{ $site['location'] }}</div>
                        </div>
                        <span class="badge rounded-pill bg-light text-muted border" style="font-size: 0.65rem;">
                            <i class="bi bi-people me-1"></i>{{ $site['count'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Weekly Attendance Trend Chart
        const attCtx = document.getElementById('attendanceChart').getContext('2d');
        const gradientPresent = attCtx.createLinearGradient(0, 0, 0, 400);
        gradientPresent.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
        gradientPresent.addColorStop(1, 'rgba(16, 185, 129, 0)');

        new Chart(attCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [
                    {
                        label: 'Present',
                        data: {!! json_encode($chartData['present']) !!},
                        borderColor: '#10b981',
                        backgroundColor: gradientPresent,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 0
                    },
                    {
                        label: 'Late',
                        data: {!! json_encode($chartData['late']) !!},
                        borderColor: '#f97316',
                        fill: false,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0
                    },
                    {
                        label: 'Absent',
                        data: {!! json_encode($chartData['absent']) !!},
                        borderColor: '#ef4444',
                        fill: false,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 6, font: { size: 10 } }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 2], color: '#f1f5f9' }, ticks: { font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });

        // Distribution Chart
        new Chart(document.getElementById('distributionChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($sites->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($sites->pluck('count')) !!},
                    backgroundColor: ['#3b82f6', '#10b981', '#f97316', '#ef4444', '#8b5cf6', '#6366f1', '#ec4899'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</x-admin-layout>

