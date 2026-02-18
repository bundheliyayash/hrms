<x-admin-layout>
    <x-slot name="header">
        Report Logic Guide
    </x-slot>

    <div class="mb-3">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-link text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="prose">
                <h2 class="h4 border-bottom pb-2 mb-4">HRMS Reports Module Manual</h2>
                
                <section class="mb-5">
                    <h3 class="h5 text-primary mb-3">1. Attendance Report</h3>
                    <p class="text-muted">Analyzes daily clock-in/out logs to verify presence and geofencing compliance.</p>
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item bg-transparent"><b>Late Status:</b> Triggered if clock-in is >15 minutes after shift start.</li>
                        <li class="list-group-item bg-transparent"><b>Net Work Time:</b> (Total Time) - (Break Duration).</li>
                        <li class="list-group-item bg-transparent"><b>Verification:</b> GPS data must be within the defined site radius.</li>
                    </ul>
                </section>

                <section class="mb-5">
                    <h3 class="h5 text-warning mb-3">2. Leave Report</h3>
                    <p class="text-muted">Tracks the lifecycle of leave applications from submission to final approval.</p>
                    <p><b>Logic:</b> Approved leaves are automatically excluded from "Absent" counts in the Muster Roll and Payroll.</p>
                </section>

                <section class="mb-5">
                    <h3 class="h5 text-success mb-3">3. Payroll Report (Wage Register)</h3>
                    <p class="text-muted">Calculates salaries based on attendance days and statutory deductions.</p>
                    <div class="p-3 bg-light rounded mb-3 border">
                        <code>Net Salary = (Basic + Allowances) - (PF + ESI + Other Deductions)</code>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item bg-transparent"><b>PF:</b> 12% of basic salary.</li>
                        <li class="list-group-item bg-transparent"><b>ESI:</b> 0.75% of total earnings.</li>
                    </ul>
                </section>

                <section>
                    <h3 class="h5 text-info mb-3">4. Muster Roll</h3>
                    <p class="text-muted">Standardized monthly view for attendance reporting.</p>
                    <p><b>Legend:</b> P = Present, A = Absent, L = Late, H = Paid Holiday.</p>
                </section>
            </div>
        </div>
    </div>
</x-admin-layout>
