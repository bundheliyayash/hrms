<x-client-portal-layout>
    <div class="d-md-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">Welcome, {{ $client->contact_person ?? $client->name }}!</h4>
            <p class="text-muted small mb-0">
                <i class="bi bi-calendar3 me-1"></i> {{ $today }} &bull; Client Portal
            </p>
        </div>
    </div>

    <style>
        .site-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.5rem;
            transition: all 0.25s ease;
            height: 100%;
        }
        .site-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.06);
            border-color: #3b82f6;
        }
        .site-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .stat-mini {
            text-align: center;
            padding: 0.5rem;
            background: #f8fafc;
            border-radius: 8px;
            flex: 1;
        }
        .stat-mini .val { font-size: 1.25rem; font-weight: 700; }
        .stat-mini .lbl { font-size: 0.65rem; color: #94a3b8; font-weight: 500; }
        .summary-card {
            background: linear-gradient(135deg, #0f4c75 0%, #3282b8 100%);
            border-radius: 14px;
            padding: 1.5rem;
            color: #fff;
        }
        .summary-stat { text-align: center; }
        .summary-stat .val { font-size: 2rem; font-weight: 700; }
        .summary-stat .lbl { font-size: 0.75rem; opacity: 0.8; }
    </style>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="summary-card">
                <div class="summary-stat">
                    <div class="val">{{ $sites->count() }}</div>
                    <div class="lbl">Active Sites</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="summary-stat">
                    <div class="val">{{ $sites->sum('present_today') }}</div>
                    <div class="lbl">Present Today</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                <div class="summary-stat">
                    <div class="val">{{ $sites->sum('absent_today') }}</div>
                    <div class="lbl">Absent Today</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sites -->
    <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2 text-primary"></i>Your Sites</h6>
    <div class="row g-3">
        @forelse($sites as $site)
            <div class="col-md-6 col-lg-4">
                <div class="site-card">
                    <div class="d-flex align-items-start mb-3">
                        <div class="site-icon bg-primary-subtle text-primary me-3">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="fw-bold mb-0 text-truncate">{{ $site['name'] }}</h6>
                            <small class="text-muted">{{ $site['address'] ?? 'No address' }}</small>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <div class="stat-mini">
                            <div class="val text-primary">{{ $site['assigned_employees'] }}</div>
                            <div class="lbl">Assigned</div>
                        </div>
                        <div class="stat-mini">
                            <div class="val text-success">{{ $site['present_today'] }}</div>
                            <div class="lbl">Present</div>
                        </div>
                        <div class="stat-mini">
                            <div class="val text-danger">{{ $site['absent_today'] }}</div>
                            <div class="lbl">Absent</div>
                        </div>
                    </div>

                    <a href="{{ route('client.attendance.form', $site['id']) }}" class="btn btn-primary btn-sm w-100 rounded-pill">
                        <i class="bi bi-pencil-square me-1"></i> Mark Attendance
                    </a>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-building display-4 mb-3 d-block"></i>
                    <p>No active sites found. Please contact the administrator.</p>
                </div>
            </div>
        @endforelse
    </div>

</x-client-portal-layout>
