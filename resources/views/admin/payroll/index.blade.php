<x-admin-layout>
    <x-slot name="header">
        Payroll Management
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-muted">Payroll History</h5>
            <a href="{{ route('admin.payroll.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-cash-stack"></i> Generate Payroll
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Employee</th>
                            <th>Month/Year</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th class="text-end px-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payrolls as $payroll)
                        <tr>
                            <td class="px-3">
                                <div class="fw-bold">{{ $payroll->user->name }}</div>
                                <div class="text-muted small">{{ $payroll->user->employeeDetail->employee_id ?? '' }}</div>
                            </td>
                            <td>{{ $payroll->month }} {{ $payroll->year }}</td>
                            <td class="fw-bold text-success">₹{{ number_format($payroll->net_salary, 2) }}</td>
                            <td>
                                <span class="badge bg-success">{{ ucfirst($payroll->status) }}</span>
                            </td>
                            <td class="text-end px-3">
                                <a href="{{ route('admin.payroll.show', $payroll->id) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-receipt"></i> Payslip
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $payrolls->links() }}
        </div>
    </div>
</x-admin-layout>
