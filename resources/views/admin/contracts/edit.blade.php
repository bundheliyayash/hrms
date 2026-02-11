<x-admin-layout>
    <x-slot name="header">
        Edit Contract: {{ $contract->contract_number }}
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-muted">Modify Contract Details</h5>
                    <span class="badge {{ $contract->status == 'active' ? 'bg-success' : 'bg-primary' }}">{{ strtoupper($contract->status) }}</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.contracts.update', $contract) }}" method="POST" id="contractForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-4 text-muted small">
                            <div class="col-md-6 font-italic">
                                <i class="bi bi-info-circle me-1"></i> Edits to active contracts should be handled carefully as they may affect current billing periods.
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Client</label>
                                <input type="text" class="form-control bg-light" value="{{ $contract->client->name }}" readonly>
                                <input type="hidden" name="client_id" value="{{ $contract->client_id }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Contract Type <span class="text-danger">*</span></label>
                                <select name="contract_type" class="form-select @error('contract_type') is-invalid @enderror" required onchange="toggleEndDate(this.value)">
                                    <option value="permanent" {{ old('contract_type', $contract->contract_type) == 'permanent' ? 'selected' : '' }}>Permanent</option>
                                    <option value="temporary" {{ old('contract_type', $contract->contract_type) == 'temporary' ? 'selected' : '' }}>Temporary</option>
                                    <option value="one_day" {{ old('contract_type', $contract->contract_type) == 'one_day' ? 'selected' : '' }}>One-Day Job</option>
                                </select>
                                @error('contract_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $contract->start_date->format('Y-m-d')) }}" required>
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3" id="endDateWrapper">
                                <label class="form-label fw-bold">End Date</label>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $contract->end_date ? $contract->end_date->format('Y-m-d') : '') }}">
                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3 pt-4">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="auto_renew" value="1" id="autoRenew" {{ old('auto_renew', $contract->auto_renew) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="autoRenew">Enable Auto-Renewal</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Billing Type <span class="text-danger">*</span></label>
                                <select name="billing_type" class="form-select @error('billing_type') is-invalid @enderror" required>
                                    <option value="per_day" {{ old('billing_type', $contract->billing_type) == 'per_day' ? 'selected' : '' }}>Per Day (Flat)</option>
                                    <option value="per_month" {{ old('billing_type', $contract->billing_type) == 'per_month' ? 'selected' : '' }}>Per Month (Fixed)</option>
                                    <option value="per_service" {{ old('billing_type', $contract->billing_type) == 'per_service' ? 'selected' : '' }}>Per Service (Hourly/Attendance)</option>
                                </select>
                                @error('billing_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Rate (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="rate_per_day" class="form-control @error('rate_per_day') is-invalid @enderror" value="{{ old('rate_per_day', $contract->rate_per_day) }}" required>
                                </div>
                                @error('rate_per_day') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Payment Terms <span class="text-danger">*</span></label>
                                <select name="payment_terms" class="form-select @error('payment_terms') is-invalid @enderror" required>
                                    <option value="weekly" {{ old('payment_terms', $contract->payment_terms) == 'weekly' ? 'selected' : '' }}>Weekly Billing</option>
                                    <option value="monthly" {{ old('payment_terms', $contract->payment_terms) == 'monthly' ? 'selected' : '' }}>Monthly Billing</option>
                                    <option value="on_completion" {{ old('payment_terms', $contract->payment_terms) == 'on_completion' ? 'selected' : '' }}>On Completion</option>
                                </select>
                                @error('payment_terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Total Workers Required (Aggregated) <span class="text-danger">*</span></label>
                                <input type="number" name="minimum_workers_required" id="minWorkers" class="form-control bg-light" value="{{ old('minimum_workers_required', $contract->minimum_workers_required) }}" readonly>
                            </div>
                        </div>

                        <div class="card mb-4 border-0 shadow-sm bg-light">
                            <div class="card-header bg-dark text-white py-2">
                                <h6 class="mb-0">Overtime (OT) Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="ot_enabled" value="1" id="otEnabled" {{ old('ot_enabled', $contract->ot_enabled) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="otEnabled">Enable OT Pay</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">OT Multiplier (e.g. 1.5x)</label>
                                        <input type="number" step="0.01" name="ot_multiplier" class="form-control form-control-sm" value="{{ old('ot_multiplier', $contract->ot_multiplier) }}">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small fw-bold">Calculate OT after (Minutes)</label>
                                        <input type="number" name="calculate_ot_after_minutes" class="form-control form-control-sm" value="{{ old('calculate_ot_after_minutes', $contract->calculate_ot_after_minutes) }}">
                                        <div class="form-text x-small">Minutes worked beyond shift duration before OT starts.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 bg-light shadow-sm border-0">
                            <div class="card-header bg-secondary text-white py-2">
                                <h6 class="mb-0">Site Assignments & Deployment Rules</h6>
                            </div>
                            <div class="card-body">
                                <div class="row" id="sitesList">
                                    @php $currentSites = $contract->sites->pluck('id')->toArray(); @endphp
                                    @foreach($contract->client->sites as $site)
                                    @php 
                                        $isSelected = in_array($site->id, $currentSites);
                                        $workersCount = $isSelected ? $contract->sites->find($site->id)->pivot->workers_required : 1;
                                    @endphp
                                    <div class="col-md-6 mb-3">
                                        <div class="card p-3 border-secondary border-opacity-25 bg-white">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input site-checkbox" type="checkbox" name="site_ids[]" value="{{ $site->id }}" id="site_{{ $site->id }}" onchange="toggleSiteInput({{ $site->id }})" {{ $isSelected ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="site_{{ $site->id }}">{{ $site->site_name }}</label>
                                            </div>
                                            <div class="site-input-group {{ $isSelected ? '' : 'd-none' }}" id="site_group_{{ $site->id }}">
                                                <label class="small text-muted mb-1">Workers Required:</label>
                                                <input type="number" name="workers_count_{{ $site->id }}" class="form-control form-control-sm worker-input" value="{{ $workersCount }}" min="1" onchange="updateTotalWorkers()">
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Terms & Conditions / Special Instructions</label>
                            <textarea name="terms_and_conditions" class="form-control" rows="4">{{ old('terms_and_conditions', $contract->terms_and_conditions) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-light border">Cancel Edits</a>
                            <button type="submit" class="btn btn-primary px-5">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleSiteInput(siteId) {
            const group = document.getElementById(`site_group_${siteId}`);
            const checkbox = document.getElementById(`site_${siteId}`);
            if (checkbox.checked) {
                group.classList.remove('d-none');
            } else {
                group.classList.add('d-none');
            }
            updateTotalWorkers();
        }

        function updateTotalWorkers() {
            let total = 0;
            const checkboxes = document.querySelectorAll('.site-checkbox:checked');
            checkboxes.forEach(cb => {
                const input = document.querySelector(`input[name="workers_count_${cb.value}"]`);
                total += parseInt(input.value) || 0;
            });
            document.getElementById('minWorkers').value = total;
        }

        function toggleEndDate(type) {
            const wrapper = document.getElementById('endDateWrapper');
            const input = wrapper.querySelector('input');
            if (type === 'one_day') {
                wrapper.classList.add('opacity-50');
                input.readOnly = true;
                input.value = document.querySelector('input[name="start_date"]').value;
            } else {
                wrapper.classList.remove('opacity-50');
                input.readOnly = false;
            }
        }
    </script>
    @endpush
</x-admin-layout>
