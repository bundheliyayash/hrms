<x-admin-layout>
    <x-slot name="header">
        Reports Dashboard
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
                        <a href="{{ route('admin.reports.attendance.excel') }}" class="btn btn-primary text-white">
                            <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i>Download Excel Report
                        </a>
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
    </div>
</x-admin-layout>
