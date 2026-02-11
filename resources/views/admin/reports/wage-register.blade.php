<x-admin-layout>
    <x-slot name="header">
        Wage Register
    </x-slot>

    <div class="mb-3 d-flex justify-content-between">
        <a href="{{ route('admin.reports.index') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
        
        <form method="GET" action="{{ route('admin.reports.wage-register') }}" class="d-flex gap-2">
            <select name="month" class="form-select form-select-sm">
                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
             <select name="year" class="form-select form-select-sm">
                @for($y=date('Y'); $y>=2024; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="window.print()">Print</button>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted">Salary Sheet - {{ $month }} {{ $year }}</h5>
        </div>
        <div class="card-body p-0">
             <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Employee</th>
                            <th>Designation</th>
                            <th>Basic Pay</th>
                            <th>Payable Days</th>
                            <th class="text-success">Earnings</th>
                            <th class="text-danger">Deductions</th>
                            <th class="bg-primary text-white">Net Pay</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $row)
                        <tr>
                            <td class="fw-bold">{{ $row->user->name }}</td>
                            <td>{{ $row->user->employeeDetail->designation ?? '-' }}</td>
                            <td>₹{{ number_format($row->basic_salary, 2) }}</td>
                            <td>{{ $row->payable_days }}</td>
                            
                            <!-- Earning Breakdown hint: Basic + Allowances -->
                            <td>
                                <div>Basic: ₹{{ number_format($row->basic_salary / $row->total_days * $row->payable_days, 2) }}</div>
                                <div class="small text-success">+ Allow: ₹{{ number_format($row->allowances, 2) }}</div>
                            </td>
                            
                            <td class="text-danger">
                                - ₹{{ number_format($row->deductions, 2) }}
                            </td>
                            
                            <td class="fw-bold fs-5 text-primary">
                                ₹{{ number_format($row->net_salary, 2) }}
                            </td>
                            
                            <td>
                                <span class="badge bg-success">{{ ucfirst($row->status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No payroll records found for this month.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
