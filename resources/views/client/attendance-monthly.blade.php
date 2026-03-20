<x-client-portal-layout>
    <style>
        .page-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; }
        .page-header { background:linear-gradient(135deg,#0f4c75 0%,#3282b8 100%); color:#fff; padding:1rem 1.5rem; }
        .step-badge { width:28px;height:28px;border-radius:50%;background:#3b82f6;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;flex-shrink:0; }
    </style>

    <div class="d-md-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-calendar-month me-2 text-primary"></i>Monthly Bulk Attendance Upload</h4>
            <p class="text-muted small mb-0">{{ $site->site_name }} &bull; Download the monthly Excel template, fill attendance for all days, then upload.</p>
        </div>
        <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill mt-2 mt-md-0">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success py-2 mb-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2 mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
    @endif
    @if(session('import_errors'))
        <div class="alert alert-warning py-2 mb-3">
            <strong><i class="bi bi-exclamation-triangle me-1"></i>Some rows had issues:</strong>
            <ul class="mb-0 mt-1">
                @foreach(session('import_errors') as $err)
                    <li class="small">{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        {{-- Left: Filters + Steps --}}
        <div class="col-lg-4">
            {{-- Site & Month Filter --}}
            <div class="page-card mb-4">
                <div class="page-header">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-funnel me-2"></i>Select Site & Month</h6>
                </div>
                <div class="p-3">
                    <form method="GET" action="">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Site</label>
                            <select name="site" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach($sites as $s)
                                    <option value="{{ $s->id }}" {{ $s->id == $site->id ? 'selected' : '' }}>{{ $s->site_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col">
                                <label class="form-label small fw-bold">Month</label>
                                <select name="month" class="form-select form-select-sm">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label small fw-bold">Year</label>
                                <select name="year" class="form-select form-select-sm">
                                    @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-sm rounded-pill w-100">
                            <i class="bi bi-arrow-clockwise me-1"></i> Apply
                        </button>
                    </form>
                </div>
            </div>

            {{-- Summary --}}
            <div class="page-card mb-4">
                <div class="p-3">
                    <div class="text-center">
                        <div class="display-6 fw-bold text-primary">{{ $recorded }}</div>
                        <div class="small text-muted">records already in system for <strong>{{ $monthName }} {{ $year }}</strong></div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between small text-muted">
                        <span><i class="bi bi-people me-1"></i>Employees this month</span>
                        <strong>{{ $employees->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mt-1">
                        <span><i class="bi bi-calendar me-1"></i>Days in month</span>
                        <strong>{{ $daysInMonth }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mt-1">
                        <span><i class="bi bi-grid me-1"></i>Max possible records</span>
                        <strong>{{ $employees->count() * $daysInMonth }}</strong>
                    </div>
                </div>
            </div>

            {{-- Steps guide --}}
            <div class="page-card">
                <div class="page-header" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>How It Works</h6>
                </div>
                <div class="p-3">
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-badge">1</div>
                        <div class="small"><strong>Download Template</strong><br><span class="text-muted">Pre-filled with all assigned employees. Already-recorded days are shown in green.</span></div>
                    </div>
                    <div class="d-flex gap-3 mb-3">
                        <div class="step-badge">2</div>
                        <div class="small"><strong>Fill Attendance</strong><br><span class="text-muted">For each day: <code>P</code> = Present, <code>HH:MM-HH:MM</code> = with clock times, blank or <code>A</code> = Absent, <code>H</code> = Holiday.</span></div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="step-badge">3</div>
                        <div class="small"><strong>Upload Filled Sheet</strong><br><span class="text-muted">Only new records are created. Already-recorded days are safely skipped.</span></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Download + Upload --}}
        <div class="col-lg-8">
            {{-- Download Template --}}
            <div class="page-card mb-4">
                <div class="page-header">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-file-earmark-arrow-down me-2"></i>Step 1 — Download Monthly Template</h6>
                </div>
                <div class="p-4 text-center">
                    <i class="bi bi-file-earmark-spreadsheet text-success" style="font-size:3rem;"></i>
                    <p class="mt-2 mb-1 fw-bold">{{ $site->site_name }} — {{ $monthName }} {{ $year }}</p>
                    <p class="small text-muted mb-3">{{ $employees->count() }} employees &bull; {{ $daysInMonth }} days &bull; {{ $recorded }} already recorded</p>
                    <a href="{{ route('client.attendance.monthly-template', ['site' => $site->id, 'month' => $month, 'year' => $year]) }}"
                       class="btn btn-success rounded-pill px-4">
                        <i class="bi bi-download me-2"></i>Download Monthly Template (.xlsx)
                    </a>
                </div>
            </div>

            {{-- Upload --}}
            <div class="page-card mb-4">
                <div class="page-header">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cloud-upload me-2"></i>Step 3 — Upload Filled Template</h6>
                </div>
                <div class="p-4">
                    <form method="POST" action="{{ route('client.attendance.upload-monthly') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="site_id" value="{{ $site->id }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Select your filled Excel file (.xlsx)</label>
                            <input type="file" name="attendance_file" accept=".xlsx,.xls"
                                   class="form-control @error('attendance_file') is-invalid @enderror" required>
                            @error('attendance_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">Max file size: 10 MB. Only use the template downloaded from Step 1.</div>
                        </div>

                        <div class="alert alert-info py-2 small">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Safe to upload multiple times.</strong> Days that are already recorded in the system will be automatically skipped — no duplicates will be created.
                        </div>

                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-cloud-upload me-2"></i>Import Monthly Attendance
                        </button>
                    </form>
                </div>
            </div>

            {{-- Employees list --}}
            <div class="page-card">
                <div class="page-header">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2"></i>Employees for {{ $monthName }} {{ $year }} ({{ $employees->count() }})</h6>
                </div>
                @if($employees->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-people display-5 d-block mb-2"></i>
                        No employees assigned to this site for {{ $monthName }} {{ $year }}.
                    </div>
                @else
                    <div class="p-3">
                        <div class="row g-2">
                            @foreach($employees as $emp)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 p-2 border rounded-3">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold"
                                             style="width:36px;height:36px;font-size:.8rem;">
                                            {{ strtoupper(substr($emp->user->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="small fw-bold text-truncate">{{ $emp->user->name }}</div>
                                            <div class="text-muted" style="font-size:.7rem;">{{ $emp->employee_id }} &bull; {{ $emp->designation ?? 'Staff' }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-client-portal-layout>
