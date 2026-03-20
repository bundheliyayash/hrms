<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <span>Reports Dashboard</span>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.admin-manual') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-book me-1"></i> Admin Manual
                </a>
                <a href="{{ route('admin.reports.guide') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-info-circle me-1"></i> Report Logic Guide
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row g-4">
        <!-- Attendance Report -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="mb-3 text-primary">
                        <i class="bi bi-calendar-range display-4"></i>
                    </div>
                    <h5 class="card-title fw-bold">Attendance Report</h5>
                    <p class="card-text text-muted">View daily attendance logs, late, and absent records for all employees.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.reports.attendance') }}" class="btn btn-outline-primary">View Log</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leave Report -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="mb-3 text-warning">
                        <i class="bi bi-file-earmark-text display-4"></i>
                    </div>
                    <h5 class="card-title fw-bold">Leave Report</h5>
                    <p class="card-text text-muted">Summary of all leave applications, approvals, and rejections.</p>
                    <a href="{{ route('admin.reports.leaves') }}" class="btn btn-outline-warning stretched-link">View Summary</a>
                </div>
            </div>
        </div>

        @if(Auth::user()->isAdmin())
        <!-- Payroll Report -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="mb-3 text-success">
                        <i class="bi bi-graph-up-arrow display-4"></i>
                    </div>
                    <h5 class="card-title fw-bold">Payroll Report</h5>
                    <p class="card-text text-muted">Financial summary of salaries disbursed.</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.reports.payroll') }}" class="btn btn-outline-success">View Financials</a>
                        <a href="{{ route('admin.reports.wage-register') }}" class="btn btn-success text-white">Wage Register</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Muster Roll -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                 <div class="card-body text-center p-4">
                    <div class="mb-3 text-info">
                        <i class="bi bi-table display-4"></i>
                    </div>
                    <h5 class="card-title fw-bold">Muster Roll</h5>
                    <p class="card-text text-muted">Monthly Grid View of Employee Attendance.</p>
                    <a href="{{ route('admin.reports.muster-roll') }}" class="btn btn-outline-info stretched-link">View Muster Roll</a>
                </div>
            </div>
        </div>

        <!-- Client Reports -->
        <div class="col-12">
            <hr class="my-2">
            <h6 class="fw-bold text-muted mb-3"><i class="bi bi-buildings me-2"></i>Client-wise Reports (Excel Export)</h6>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0" style="border-left:4px solid #0f4c75 !important;">
                <div class="card-body p-4">
                    <div class="mb-3 text-primary">
                        <i class="bi bi-file-earmark-spreadsheet display-4"></i>
                    </div>
                    <h5 class="card-title fw-bold">Client Salary Register</h5>
                    <p class="card-text text-muted small">Multi-sheet Excel: Summary register + individual payslip for each employee.</p>
                    <form method="GET" action="{{ route('admin.reports.client-export') }}">
                        <input type="hidden" name="type" value="salary">
                        <div class="mb-2">
                            <select name="client_id" class="form-select form-select-sm" required>
                                <option value="">Select Client</option>
                                @foreach(\App\Models\Client::orderBy('name')->get() as $cl)
                                    <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col">
                                <select name="month" class="form-select form-select-sm" required>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('M') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col">
                                <select name="year" class="form-select form-select-sm" required>
                                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-sm w-100 rounded-pill">
                            <i class="bi bi-download me-1"></i> Download Salary Register
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0" style="border-left:4px solid #10b981 !important;">
                <div class="card-body p-4">
                    <div class="mb-3 text-success">
                        <i class="bi bi-calendar-check display-4"></i>
                    </div>
                    <h5 class="card-title fw-bold">Client Attendance Register</h5>
                    <p class="card-text text-muted small">2-sheet Excel: Daily P/A matrix + attendance summary for all employees at client sites.</p>
                    <form method="GET" action="{{ route('admin.reports.client-export') }}">
                        <input type="hidden" name="type" value="attendance">
                        <div class="mb-2">
                            <select name="client_id" class="form-select form-select-sm" required>
                                <option value="">Select Client</option>
                                @foreach(\App\Models\Client::orderBy('name')->get() as $cl)
                                    <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col">
                                <select name="month" class="form-select form-select-sm" required>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('M') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col">
                                <select name="year" class="form-select form-select-sm" required>
                                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-success btn-sm w-100 rounded-pill">
                            <i class="bi bi-download me-1"></i> Download Attendance Register
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0" style="border-left:4px solid #f97316 !important;">
                <div class="card-body p-4">
                    <div class="mb-3 text-warning">
                        <i class="bi bi-receipt display-4"></i>
                    </div>
                    <h5 class="card-title fw-bold">Client Wage Muster</h5>
                    <p class="card-text text-muted small">Statutory wage register with PF, ESIC, PT breakdown per employee for a specific client.</p>
                    <form method="GET" action="{{ route('admin.reports.client-export') }}">
                        <input type="hidden" name="type" value="wage_muster">
                        <div class="mb-2">
                            <select name="client_id" class="form-select form-select-sm" required>
                                <option value="">Select Client</option>
                                @foreach(\App\Models\Client::orderBy('name')->get() as $cl)
                                    <option value="{{ $cl->id }}">{{ $cl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col">
                                <select name="month" class="form-select form-select-sm" required>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('M') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col">
                                <select name="year" class="form-select form-select-sm" required>
                                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-warning btn-sm w-100 rounded-pill">
                            <i class="bi bi-download me-1"></i> Download Wage Muster
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-admin-layout>
