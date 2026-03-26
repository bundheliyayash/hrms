<x-admin-layout>
    <x-slot name="header">
        Payslip Details
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body p-5" id="payslip">
            
            <div class="text-center border-bottom pb-4 mb-4">
                <h2 class="fw-bold">HRMS System</h2>
                <p class="text-muted">Salary Slip for <b>{{ $payroll->month }} {{ $payroll->year }}</b></p>
            </div>

            <div class="row mb-5">
                <div class="col-md-6">
                    <p class="small text-uppercase text-muted mb-1">Employee Details</p>
                    <h5 class="fw-bold">{{ $payroll->user->name }}</h5>
                    <p class="mb-0">ID: {{ $payroll->user->employeeDetail->employee_id ?? 'N/A' }}</p>
                    <p>Designation: {{ $payroll->user->employeeDetail->designation ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="small text-uppercase text-muted mb-1">Attendance Summary</p>
                    <div class="small">
                        <div>Payable Days: <b>{{ $payroll->payable_days }}</b></div>
                        <div>Present Days: <b>{{ $payroll->present_days }}</b></div>
                        <div>Paid Holidays: <b>{{ $payroll->paid_holidays }}</b></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Payable Days</td>
                                <td class="text-end">{{ $payroll->payable_days }} Days</td>
                            </tr>
                            @if($payroll->ot_hours > 0)
                            <tr>
                                <td>Overtime ({{ $payroll->ot_hours }} Hrs)</td>
                                <td class="text-end text-success">+ ₹{{ number_format($payroll->ot_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td>Basic Salary ({{ $payroll->per_day_salary }}/day)</td>
                                <td class="text-end fw-bold">₹{{ number_format($payroll->basic_salary, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Allowances</td>
                                <td class="text-end text-success">+ ₹{{ number_format($payroll->allowances, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Other Deductions</td>
                                <td class="text-end text-danger">- ₹{{ number_format($payroll->deductions, 2) }}</td>
                            </tr>
                            @php
                                $pfPct  = \App\Models\Setting::get('pf_percentage', 12);
                                $esiPct = \App\Models\Setting::get('esi_percentage', 0.75);
                            @endphp
                            <tr>
                                <td>PF Deduction ({{ $pfPct }}%)</td>
                                <td class="text-end text-danger">- ₹{{ number_format($payroll->pf_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>ESI Deduction ({{ $esiPct }}%)</td>
                                <td class="text-end text-danger">- ₹{{ number_format($payroll->esi_amount, 2) }}</td>
                            </tr>
                            <tr class="table-active">
                                <td class="fw-bold h5 mb-0">Net Salary</td>
                                <td class="text-end fw-bold h5 mb-0">₹{{ number_format($payroll->net_salary, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center mt-5 d-print-none">
                <button onclick="window.print()" class="btn btn-dark">
                    <i class="bi bi-printer"></i> Print Payslip
                </button>
                <a href="{{ route('admin.payroll.index') }}" class="btn btn-secondary ms-2">Back to List</a>
            </div>

        </div>
    </div>
</x-admin-layout>
