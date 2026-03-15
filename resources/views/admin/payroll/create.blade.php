<x-admin-layout>
    <x-slot name="header">
        Generate Payroll
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted">New Payroll Entry</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.payroll.store') }}" id="payroll-form">
                @csrf

                <div class="row g-3">
                    <!-- Selection Section -->
                    <div class="col-md-4">
                        <label for="user_id" class="form-label fw-bold">Employee</label>
                        <select name="user_id" id="user_id" class="form-select" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="month_year" class="form-label fw-bold">Select Month</label>
                        <input type="month" class="form-control" id="month_year" required>
                        <!-- Hidden fields to store split month/year for backend -->
                        <input type="hidden" name="month" id="month_input">
                        <input type="hidden" name="year" id="year_input">
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" id="fetch-btn" class="btn btn-info text-white w-100">
                            <i class="bi bi-calculator"></i> Fetch & Calculate
                        </button>
                    </div>

                    <div class="col-12"><hr class="text-muted"></div>

                    <!-- Calculation Results -->
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Total Days in Month</label>
                        <input type="number" class="form-control bg-light" name="total_days" id="total_days" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Target Basic Salary</label>
                        <input type="number" class="form-control bg-light" name="basic_salary" id="basic_salary" readonly>
                    </div>
                     <div class="col-md-3">
                        <label class="form-label text-muted small">Per Day Salary</label>
                        <input type="number" class="form-control bg-light" name="per_day_salary" id="per_day_salary" step="0.01" readonly>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-success">Payable Days</label>
                        <input type="number" class="form-control border-success bg-light fw-bold" name="payable_days" id="payable_days" step="0.5" readonly> 
                        <div class="form-text small" id="attendance-breakdown">Present: 0, Half: 0, Leave: 0, Holiday: 0</div>
                        <input type="hidden" name="present_days" id="present_days">
                        <input type="hidden" name="paid_holidays" id="paid_holidays_hidden">
                    </div>

                    <div class="col-12"><hr class="text-muted"></div>

                    <!-- Adjustments -->
                    <div class="col-md-4">
                        <label for="allowances" class="form-label fw-bold">Allowances (+)</label>
                        <input type="number" class="form-control" name="allowances" id="allowances" value="0" step="0.01">
                    </div>

                    <div class="col-md-3">
                        <label for="deductions" class="form-label fw-bold">Other Deductions (-)</label>
                        <input type="number" class="form-control" name="deductions" id="deductions" value="0" step="0.01">
                    </div>

                    <div class="col-md-2 d-flex align-items-end mb-3">
                        <div class="form-check form-switch pb-2">
                            <input class="form-check-input" type="checkbox" id="payOT" checked>
                            <label class="form-check-label fw-bold" for="payOT">Pay OT?</label>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label small fw-bold">OT Hours</label>
                        <input type="number" step="0.5" name="ot_hours" id="otHours" class="form-control" value="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label small fw-bold text-success">OT Earnings (₹)</label>
                        <input type="number" step="0.01" name="ot_amount" id="otAmount" class="form-control fw-bold text-success" value="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-danger small">PF Deduction (12%)</label>
                        <input type="number" class="form-control bg-light text-danger border-danger-subtle" name="pf_amount" id="pf_amount" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-danger small">ESI Deduction (0.75%)</label>
                        <input type="number" class="form-control bg-light text-danger border-danger-subtle" name="esi_amount" id="esi_amount" readonly>
                    </div>

                    <div class="col-md-3">
                        <label for="pt_amount" class="form-label text-danger small">Prof. Tax (PT) (-)</label>
                        <input type="number" class="form-control border-danger-subtle" name="pt_amount" id="pt_amount" value="0" step="0.01">
                    </div>

                    <div class="col-md-3">
                        <label for="advance_amount" class="form-label text-danger small">Advance / Fine (-)</label>
                        <input type="number" class="form-control border-danger-subtle" name="advance_amount" id="advance_amount" value="0" step="0.01">
                    </div>

                    <div class="col-12"><hr class="text-muted"></div>

                    <div class="col-md-3">
                         <label for="hra" class="form-label small fw-bold">HRA (+)</label>
                         <input type="number" step="0.01" name="hra" id="hra" class="form-control" value="0">
                    </div>

                    <div class="col-md-3">
                         <label for="washing_allowance" class="form-label small fw-bold">Washing Allow. (+)</label>
                         <input type="number" step="0.01" name="washing_allowance" id="washing_allowance" class="form-control" value="0">
                    </div>

                    <div class="col-md-6 text-end">
                         <label class="form-label fw-bold text-muted small">Gross Salary (Before Deductions)</label>
                         <input type="number" step="0.01" name="gross_salary" id="gross_salary" class="form-control bg-light fw-bold text-end" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-primary display-6" style="font-size: 1.2rem;">Net Salary</label>
                        <input type="number" class="form-control form-control-lg border-primary fw-bold" name="net_salary" id="net_salary" step="0.01" readonly>
                    </div>

                    <div class="col-12"><hr class="text-muted"></div>

                    <!-- Payout Splitting -->
                    <div class="col-md-6">
                        <label for="bank_amount" class="form-label fw-bold text-info"><i class="bi bi-bank me-1"></i> Bank Payout</label>
                        <input type="number" class="form-control border-info fw-bold" name="bank_amount" id="bank_amount" step="0.01" value="0">
                        <div class="form-text small">Amount to be paid via Bank Transfer</div>
                    </div>

                    <div class="col-md-6">
                        <label for="cash_amount" class="form-label fw-bold text-warning"><i class="bi bi-cash-stack me-1"></i> Cash Payout</label>
                        <input type="number" class="form-control border-warning fw-bold" name="cash_amount" id="cash_amount" step="0.01" value="0">
                        <div class="form-text small">Amount to be paid in Cash</div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('admin.payroll.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-success" id="submit-btn" disabled>
                        <i class="bi bi-check-circle"></i> Generate Payroll
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- AJAX Script -->
    <script>
        const fetchBtn = document.getElementById('fetch-btn');
        const userId = document.getElementById('user_id');
        const monthYear = document.getElementById('month_year');
        
        // Outputs
        const totalDaysOut = document.getElementById('total_days');
        const basicSalaryOut = document.getElementById('basic_salary');
        const perDaySalaryOut = document.getElementById('per_day_salary');
        const payableDaysOut = document.getElementById('payable_days');
        const presentDaysHidden = document.getElementById('present_days');
        const breakdownText = document.getElementById('attendance-breakdown');
        
        const allowancesIn = document.getElementById('allowances');
        const deductionsIn = document.getElementById('deductions');
        const otHoursOut = document.getElementById('otHours');
        const otAmountOut = document.getElementById('otAmount');
        const pfAmountOut = document.getElementById('pf_amount');
        const esiAmountOut = document.getElementById('esi_amount');
        const netSalaryOut = document.getElementById('net_salary');
        
        const monthInput = document.getElementById('month_input');
        const yearInput = document.getElementById('year_input');
        const submitBtn = document.getElementById('submit-btn');

        let baseEarnings = 0;

        const payOTToggle = document.getElementById('payOT');
        let rawOtHours = 0;
        let rawOtAmount = 0;

        fetchBtn.addEventListener('click', async () => {
            if(!userId.value || !monthYear.value) {
                alert('Please select an Employee and a Month.');
                return;
            }

            // Set hidden inputs
            const date = new Date(monthYear.value + '-01'); // Force 1st day to avoid timezone weirdness on 31st
            const monthNames = ["January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];
            monthInput.value = monthNames[date.getMonth()];
            yearInput.value = date.getFullYear();

            // Fetch Data
            try {
                const response = await fetch(`{{ route('admin.payroll.stats') }}?user_id=${userId.value}&month_year=${monthYear.value}`);
                const data = await response.json();

                if(!data) return;

                // Populate Form
                totalDaysOut.value = data.total_days;
                basicSalaryOut.value = data.basic_salary;
                perDaySalaryOut.value = data.per_day_salary;
                payableDaysOut.value = data.payable_days;
                presentDaysHidden.value = data.present_days; // Store for DB
                document.getElementById('paid_holidays_hidden').value = data.paid_holidays;
                
                breakdownText.innerText = `Present: ${data.present_days}, Half: ${data.half_days}, Leave: ${data.paid_leave_days}, Holiday: ${data.paid_holidays}`;
                
                baseEarnings = parseFloat(data.base_earnings);
                rawOtHours = parseFloat(data.ot_hours) || 0;
                rawOtAmount = parseFloat(data.ot_earnings) || 0;
                
                if (payOTToggle.checked) {
                    otHoursOut.value = rawOtHours;
                    otAmountOut.value = rawOtAmount;
                } else {
                    otHoursOut.value = 0;
                    otAmountOut.value = 0;
                }

                pfAmountOut.value = data.pf_amount;
                esiAmountOut.value = data.esi_amount;
                calculateNet();
                
                submitBtn.disabled = false;

            } catch (error) {
                console.error(error);
                alert('Error fetching attendance data.');
            }
        });

        payOTToggle.addEventListener('change', () => {
            if (payOTToggle.checked) {
                otHoursOut.value = rawOtHours;
                otAmountOut.value = rawOtAmount;
            } else {
                otHoursOut.value = 0;
                otAmountOut.value = 0;
            }
            calculateNet();
        });

        const bankAmountIn = document.getElementById('bank_amount');
        const cashAmountIn = document.getElementById('cash_amount');
        
        const hraIn = document.getElementById('hra');
        const washingIn = document.getElementById('washing_allowance');
        const grossOut = document.getElementById('gross_salary');
        const ptIn = document.getElementById('pt_amount');
        const advanceIn = document.getElementById('advance_amount');

        function calculateNet() {
            const allow = parseFloat(allowancesIn.value) || 0;
            const hra = parseFloat(hraIn.value) || 0;
            const washing = parseFloat(washingIn.value) || 0;
            const ot = parseFloat(otAmountOut.value) || 0;
            
            const gross = baseEarnings + ot + allow + hra + washing;
            grossOut.value = gross.toFixed(2);

            const deduct = parseFloat(deductionsIn.value) || 0;
            const pf = parseFloat(pfAmountOut.value) || 0;
            const esi = parseFloat(esiAmountOut.value) || 0;
            const pt = parseFloat(ptIn.value) || 0;
            const advance = parseFloat(advanceIn.value) || 0;
            
            const totalDeductions = deduct + pf + esi + pt + advance;
            const net = gross - totalDeductions;
            
            const netVal = net.toFixed(2);
            netSalaryOut.value = netVal;
            
            // Default to Bank payout
            bankAmountIn.value = netVal;
            cashAmountIn.value = "0.00";
        }

        // Handle Bank/Cash splitting manual changes
        bankAmountIn.addEventListener('input', () => {
            const net = parseFloat(netSalaryOut.value) || 0;
            const bank = parseFloat(bankAmountIn.value) || 0;
            cashAmountIn.value = Math.max(0, net - bank).toFixed(2);
        });

        cashAmountIn.addEventListener('input', () => {
            const net = parseFloat(netSalaryOut.value) || 0;
            const cash = parseFloat(cashAmountIn.value) || 0;
            bankAmountIn.value = Math.max(0, net - cash).toFixed(2);
        });

        allowancesIn.addEventListener('input', calculateNet);
        deductionsIn.addEventListener('input', calculateNet);
        hraIn.addEventListener('input', calculateNet);
        washingIn.addEventListener('input', calculateNet);
        ptIn.addEventListener('input', calculateNet);
        advanceIn.addEventListener('input', calculateNet);
        otHoursOut.addEventListener('input', calculateNet);
        otAmountOut.addEventListener('input', calculateNet);

    </script>
</x-admin-layout>
