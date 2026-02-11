<x-admin-layout>
    <x-slot name="header">
        My Payslip
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body p-5" id="payslip">
            
            <div class="text-center border-bottom pb-4 mb-4">
                <h2 class="fw-bold text-primary">Clean HRMS</h2>
                <p class="text-muted">Salary Slip for <b>{{ $payroll->month }} {{ $payroll->year }}</b></p>
            </div>

            <div class="row mb-5">
                <div class="col-md-6">
                    <p class="small text-uppercase text-muted mb-1">Employee Info</p>
                    <h5 class="fw-bold">{{ Auth::user()->name }}</h5>
                    <p class="mb-0 text-muted">{{ Auth::user()->employeeDetail->employee_id ?? '' }}</p>
                    <p class="text-muted small">{{ Auth::user()->employeeDetail->designation ?? '' }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="small text-uppercase text-muted mb-1">Statement #</p>
                    <h5 class="fw-bold">PAY-{{ $payroll->id }}</h5>
                    <p class="text-muted small">Generated on: {{ $payroll->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Earnings & Deductions</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Basic Salary (Allocated)</td>
                                <td class="text-end fw-bold">₹{{ number_format($payroll->basic_salary, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-success">Allowances / Incentives</td>
                                <td class="text-end text-success">+ ₹{{ number_format($payroll->allowances, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-danger">Deductions / Tax</td>
                                <td class="text-end text-danger">- ₹{{ number_format($payroll->deductions, 2) }}</td>
                            </tr>
                            <tr class="table-primary border-primary">
                                <td class="fw-bold h5 mb-0">Total Net Payable</td>
                                <td class="text-end fw-bold h5 mb-0">₹{{ number_format($payroll->net_salary, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center mt-5 d-print-none">
                <button onclick="window.print()" class="btn btn-primary px-4">
                    <i class="bi bi-printer me-2"></i> Print My Slip
                </button>
                <a href="{{ route('employee.payroll.index') }}" class="btn btn-outline-secondary ms-2 px-4">Back to List</a>
            </div>

        </div>
    </div>
</x-admin-layout>
