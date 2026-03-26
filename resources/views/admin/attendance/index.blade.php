<x-admin-layout>
<style>
    /* ── Stats cards ──────────────────────────────────────────── */
    .att-stat { background:#fff; border:1px solid #e2e8f0; border-radius:12px;
                padding:1rem 1.25rem; display:flex; align-items:center; gap:1rem; }
    .att-stat-icon { width:44px; height:44px; border-radius:10px; display:flex;
                     align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
    .att-stat-val  { font-size:1.6rem; font-weight:700; line-height:1; }
    .att-stat-lbl  { font-size:.7rem; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }

    /* ── Table ────────────────────────────────────────────────── */
    .table thead th { font-size:.68rem; font-weight:700; text-transform:uppercase;
                      letter-spacing:.05em; color:#64748b; border-bottom:1px solid #f1f5f9;
                      background:#f8fafc; padding:.75rem 1rem; }
    .table tbody td { border-bottom:1px solid #f8fafc; font-size:.83rem;
                      vertical-align:middle; padding:.75rem 1rem; }
    .table tbody tr:hover td { background:#fafbfc; }

    /* ── Status chips ─────────────────────────────────────────── */
    .chip { display:inline-flex; align-items:center; gap:.3rem; font-size:.68rem;
            font-weight:600; padding:.25rem .65rem; border-radius:6px; }
    .chip-present  { background:#ecfdf5; color:#059669; }
    .chip-late     { background:#fff7ed; color:#d97706; }
    .chip-half_day { background:#eff6ff; color:#2563eb; }
    .chip-absent   { background:#fef2f2; color:#dc2626; }
    .chip-holiday  { background:#f3e8ff; color:#7c3aed; }

    /* ── Source tag ───────────────────────────────────────────── */
    .src-tag { font-size:.6rem; font-weight:600; text-transform:uppercase;
               padding:.15rem .45rem; border-radius:4px; letter-spacing:.04em; }
    .src-client { background:#dbeafe; color:#1d4ed8; }
    .src-admin  { background:#f0fdf4; color:#15803d; }
    .src-app    { background:#fef3c7; color:#92400e; }

    /* ── Location badge ───────────────────────────────────────── */
    .loc-ok   { color:#16a34a; }
    .loc-warn { color:#d97706; }
    .loc-bad  { color:#dc2626; }

    /* ── Duration ─────────────────────────────────────────────── */
    .dur { font-size:.78rem; color:#475569; font-weight:600; }

    /* ── Avatar ───────────────────────────────────────────────── */
    .emp-avatar { width:36px; height:36px; border-radius:50%; display:flex;
                  align-items:center; justify-content:center; font-weight:700;
                  font-size:.75rem; flex-shrink:0; }

    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.4} }
    .blink { animation:blink 1.5s infinite; }
</style>

<div class="py-4">

    {{-- ── Page header ──────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <h2 class="h4 fw-bold mb-1">Attendance Central</h2>
            <p class="text-muted small mb-0">
                <i class="bi bi-calendar3 me-1"></i>
                Daily attendance monitoring &amp; timesheet management
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.attendance.requests') }}"
               class="btn btn-outline-warning rounded-pill px-4 fw-semibold">
                <i class="bi bi-bell-fill me-1"></i> Correction Requests
            </a>
            <a href="{{ route('admin.attendance.export', ['date' => $date]) }}"
               class="btn btn-success rounded-pill px-4 fw-semibold text-white">
                <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Export Excel
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4 shadow-sm py-2">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    {{-- ── Summary stats cards ──────────────────────────────────────── --}}
    @php
        $allAtt    = $attendances->getCollection();
        $cntPresent  = $allAtt->where('status','present')->count();
        $cntLate     = $allAtt->where('status','late')->count();
        $cntHalf     = $allAtt->where('status','half_day')->count();
        $cntTotal    = $allAtt->count();
        $cntPortal   = $allAtt->where('source','client_portal')->count();
        $cntVerified = $allAtt->where('is_verified', true)->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="att-stat shadow-sm">
                <div class="att-stat-icon bg-success-subtle text-success"><i class="bi bi-person-check-fill"></i></div>
                <div>
                    <div class="att-stat-val text-success">{{ $cntPresent }}</div>
                    <div class="att-stat-lbl">Present</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="att-stat shadow-sm">
                <div class="att-stat-icon bg-warning-subtle text-warning"><i class="bi bi-clock-history"></i></div>
                <div>
                    <div class="att-stat-val text-warning">{{ $cntLate }}</div>
                    <div class="att-stat-lbl">Late</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="att-stat shadow-sm">
                <div class="att-stat-icon bg-primary-subtle text-primary"><i class="bi bi-arrows-collapse"></i></div>
                <div>
                    <div class="att-stat-val text-primary">{{ $cntHalf }}</div>
                    <div class="att-stat-lbl">Half Day</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="att-stat shadow-sm">
                <div class="att-stat-icon bg-info-subtle text-info"><i class="bi bi-building-check"></i></div>
                <div>
                    <div class="att-stat-val text-info">{{ $cntPortal }}</div>
                    <div class="att-stat-lbl">Via Client Portal</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tab nav ───────────────────────────────────────────────────── --}}
    <ul class="nav nav-pills bg-white border rounded-pill p-1 d-inline-flex shadow-sm mb-4"
        id="attendanceTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active rounded-pill px-4 fw-semibold"
                    data-bs-toggle="tab" data-bs-target="#daily" type="button">
                <i class="bi bi-table me-1"></i> Daily Log
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-pill px-4 fw-semibold"
                    data-bs-toggle="tab" data-bs-target="#employees" type="button">
                <i class="bi bi-people me-1"></i> Staff Directory
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ── TAB 1: Daily Log ─────────────────────────────────────── --}}
        <div class="tab-pane fade show active" id="daily">
            <div class="card border-0 shadow-sm overflow-hidden">

                {{-- Card header with date picker --}}
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <div class="fw-bold">
                                <i class="bi bi-calendar-check text-primary me-1"></i>
                                {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}
                            </div>
                            <div class="text-muted small">
                                {{ $cntTotal }} record(s) found &nbsp;&bull;&nbsp;
                                {{ $cntVerified }} verified
                            </div>
                        </div>
                        <a href="{{ route('admin.reports.attendance', ['month' => date('m', strtotime($date)), 'year' => date('Y', strtotime($date))]) }}"
                           class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-calendar3-range me-1"></i> Monthly View
                        </a>
                    </div>
                    <form action="{{ route('admin.attendance.index') }}" method="GET"
                          class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0 text-muted small fw-semibold">Date:</label>
                        <input type="date" name="date"
                               class="form-control form-control-sm"
                               style="width:160px;"
                               value="{{ $date }}" onchange="this.form.submit()">
                    </form>
                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th style="width:220px;">Employee</th>
                                <th>Client / Site</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Duration</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Source</th>
                                <th class="text-center">Location</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $att)
                            <tr>
                                {{-- Employee --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="emp-avatar bg-primary-subtle text-primary">
                                            {{ strtoupper(substr($att->user->name ?? 'U', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $att->user->name }}</div>
                                            <div class="text-muted" style="font-size:.68rem;">
                                                {{ $att->user->employeeDetail->employee_id ?? 'N/A' }}
                                                &bull; {{ $att->user->employeeDetail->designation ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Client / Site --}}
                                <td>
                                    <div class="fw-semibold small">{{ $att->site->client->name ?? '—' }}</div>
                                    <div class="text-muted" style="font-size:.7rem;">
                                        <i class="bi bi-geo-alt text-primary me-1"></i>
                                        {{ $att->site->site_name ?? '—' }}
                                    </div>
                                </td>

                                {{-- Clock In --}}
                                <td class="fw-semibold">
                                    {{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '—' }}
                                </td>

                                {{-- Clock Out --}}
                                <td class="fw-semibold">
                                    @if($att->clock_out)
                                        {{ \Carbon\Carbon::parse($att->clock_out)->format('h:i A') }}
                                    @else
                                        <span class="text-success blink fw-bold" style="font-size:.75rem;">● ON DUTY</span>
                                    @endif
                                </td>

                                {{-- Duration --}}
                                <td>
                                    @if($att->duration_minutes)
                                        <span class="dur">
                                            {{ floor($att->duration_minutes / 60) }}h
                                            {{ $att->duration_minutes % 60 }}m
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="text-center">
                                    @php
                                        $s = $att->status ?? 'present';
                                    @endphp
                                    <span class="chip chip-{{ $s }}">
                                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                                    </span>
                                </td>

                                {{-- Source --}}
                                <td class="text-center">
                                    @php $src = $att->source ?? 'admin'; @endphp
                                    <span class="src-tag src-{{ str_replace('_portal','',str_replace('client_portal','client',$src)) }}">
                                        {{ $src === 'client_portal' ? 'Client' : ucfirst($src) }}
                                    </span>
                                </td>

                                {{-- Location / Verified --}}
                                <td class="text-center">
                                    @if($att->is_verified)
                                        <span class="loc-ok" title="Location verified ({{ $att->distance_detected }}m)">
                                            <i class="bi bi-geo-alt-fill"></i>
                                        </span>
                                    @else
                                        <span class="loc-bad" title="Suspicious location">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                        </span>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.attendance.index', ['user_id' => $att->user_id]) }}"
                                       class="btn btn-light btn-sm rounded-pill px-3 border fw-semibold">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x display-6 d-block mb-2 text-secondary"></i>
                                    No attendance records for
                                    <strong>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</strong>.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($attendances->hasPages())
                    <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                        {{ $attendances->appends(['date' => $date])->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ── TAB 2: Staff Directory ────────────────────────────────── --}}
        <div class="tab-pane fade" id="employees">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-people-fill text-primary me-2"></i>
                        Staff Directory &mdash; Site Deployment
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th>Client</th>
                                <th>Active Site</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $emp)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="emp-avatar" style="background:#f1f5f9;color:#475569;">
                                            {{ strtoupper(substr($emp->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $emp->name }}</div>
                                            <div class="text-muted" style="font-size:.68rem;">
                                                {{ $emp->employeeDetail->employee_id ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-semibold" style="font-size:.7rem;">
                                        {{ $emp->employeeDetail->designation ?? 'Staff' }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $emp->employeeDetail->department ?? '—' }}</td>
                                <td class="fw-semibold small">{{ $emp->employeeDetail->site->client->name ?? '—' }}</td>
                                <td class="text-muted small">
                                    <i class="bi bi-geo-alt text-danger me-1"></i>
                                    {{ $emp->employeeDetail->site->site_name ?? '—' }}
                                </td>
                                <td class="text-center">
                                    @if($emp->status === 'active')
                                        <span class="chip chip-present">Active</span>
                                    @else
                                        <span class="chip chip-absent">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.attendance.index', ['user_id' => $emp->id]) }}"
                                       class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
                                        Timesheet
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No employees found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>{{-- /tab-content --}}
</div>
</x-admin-layout>
