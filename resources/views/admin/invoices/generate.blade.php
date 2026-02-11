<x-admin-layout>
    <x-slot name="header">
        Generate Client Invoice
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom text-center">
                    <h5 class="mb-0 text-muted">Intelligent Billing Engine</h5>
                </div>
                <div class="card-body py-4">
                    <div class="alert alert-info border-0 shadow-xs mb-4">
                        <i class="bi bi-info-circle me-2"></i> This process will scan all <strong>completed attendance</strong> (clock-out required) within the selected period and apply the rates defined in the contract.
                    </div>

                    <form action="{{ route('admin.invoices.generate') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Step 1: Select Contract <span class="text-danger">*</span></label>
                            <select name="contract_id" id="contractSelect" class="form-select @error('contract_id') is-invalid @enderror" required onchange="updateSummary()">
                                <option value="">Choose active contract...</option>
                                @foreach($clients as $client)
                                    <optgroup label="{{ $client->name }}">
                                        @foreach($client->contracts as $contract)
                                            @if($contract->status == 'active')
                                                <option value="{{ $contract->id }}" 
                                                        data-client="{{ $client->name }}" 
                                                        data-rate="{{ $contract->rate_per_day }}"
                                                        data-type="{{ $contract->billing_type }}"
                                                        {{ request('contract_id') == $contract->id ? 'selected' : '' }}>
                                                    #{{ $contract->contract_number }} ({{ ucfirst($contract->contract_type) }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <small class="text-muted italic">Only active contracts are eligible for invoicing.</small>
                            @error('contract_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-4">
                            <label class="form-label fw-bold mb-0">Step 2: Billing Period <span class="text-danger">*</span></label>
                            <div class="col-md-6">
                                <label class="small text-muted">From Date</label>
                                <input type="date" name="billing_period_start" class="form-control" value="{{ date('Y-m-01', strtotime('last month')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">To Date</label>
                                <input type="date" name="billing_period_end" class="form-control" value="{{ date('Y-m-t', strtotime('last month')) }}" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Step 3: Invoice Terms</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="small text-muted">Due Date <span class="text-danger">*</span></label>
                                    <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+15 days')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted">Tax Calculation</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" value="18" disabled>
                                        <span class="input-group-text">% GST</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="summaryCard" class="card bg-light border-0 mb-4 d-none shadow-xs">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary mb-2">Invoicing Summary</h6>
                                <div class="row small">
                                    <div class="col-6 text-muted">Contract Rate:</div>
                                    <div class="col-6 text-end fw-bold" id="summRate">₹0.00</div>
                                    <div class="col-6 text-muted">Billing Mode:</div>
                                    <div class="col-6 text-end" id="summType">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid pt-2">
                            <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold">
                                <i class="bi bi-gear-wide-connected me-2"></i> RUN BILLING PROCESS
                            </button>
                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-link text-muted mt-2 text-decoration-none">Back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateSummary() {
            const select = document.getElementById('contractSelect');
            const card = document.getElementById('summaryCard');
            const selected = select.options[select.selectedIndex];
            
            if (select.value === "") {
                card.classList.add('d-none');
                return;
            }

            card.classList.remove('d-none');
            document.getElementById('summRate').innerText = '₹' + parseFloat(selected.dataset.rate).toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('summType').innerText = selected.dataset.type.replace('_', ' ').toUpperCase();
        }

        @if(request('contract_id'))
            updateSummary();
        @endif
    </script>
    @endpush
</x-admin-layout>
