<x-admin-layout>
    <x-slot name="header">
        Payroll Report
    </x-slot>

    <div class="mb-3">
        <a href="{{ route('admin.reports.index') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="card shadow-sm mb-4 border-success">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title text-success mb-1">Total Salary Disbursed (Paid)</h5>
                <p class="text-muted mb-0 small">Aggregate of all paid salaries to date.</p>
            </div>
            <div class="h2 text-success fw-bold mb-0">
                ₹{{ number_format($totalPayrollPaid, 2) }}
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted">Transaction History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Month/Year</th>
                            <th>Employee</th>
                            <th>Basic</th>
                            <th>Allowances</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payrolls as $payroll)
                        <tr>
                            <td class="px-3">{{ $payroll->month }} {{ $payroll->year }}</td>
                            <td class="fw-bold">{{ $payroll->user->name }}</td>
                            <td>₹{{ number_format($payroll->basic_salary, 2) }}</td>
                            <td class="text-success">+₹{{ number_format($payroll->allowances, 2) }}</td>
                            <td class="text-danger">-₹{{ number_format($payroll->deductions, 2) }}</td>
                            <td class="fw-bold">₹{{ number_format($payroll->net_salary, 2) }}</td>
                            <td>
                                <span class="badge bg-success">{{ ucfirst($payroll->status) }}</span>
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
