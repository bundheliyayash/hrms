<x-admin-layout>
    <x-slot name="header">
        Create New Contract
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-muted">Contract Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.contracts.store') }}" method="POST" id="contractForm">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Select Client <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required onchange="loadClientSites(this.value)">
                                    <option value="">Choose Client...</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Contract Type <span class="text-danger">*</span></label>
                                <select name="contract_type" class="form-select @error('contract_type') is-invalid @enderror" required onchange="toggleEndDate(this.value)">
                                    <option value="permanent" {{ old('contract_type') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                                    <option value="temporary" {{ old('contract_type') == 'temporary' ? 'selected' : '' }}>Temporary</option>
                                    <option value="one_day" {{ old('contract_type') == 'one_day' ? 'selected' : '' }}>One-Day Job</option>
                                </select>
                                @error('contract_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3" id="endDateWrapper">
                                <label class="form-label fw-bold">End Date</label>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
                                <small class="text-muted">Leave empty for continuous permanent contracts.</small>
                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3 pt-4">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="auto_renew" value="1" id="autoRenew" {{ old('auto_renew') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="autoRenew">Enable Auto-Renewal</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Billing Type <span class="text-danger">*</span></label>
                                <select name="billing_type" class="form-select @error('billing_type') is-invalid @enderror" required>
                                    <option value="per_day" {{ old('billing_type') == 'per_day' ? 'selected' : '' }}>Per Day (Flat)</option>
                                    <option value="per_month" {{ old('billing_type') == 'per_month' ? 'selected' : '' }}>Per Month (Fixed)</option>
                                    <option value="per_service" {{ old('billing_type') == 'per_service' ? 'selected' : '' }}>Per Service (Hourly/Attendance)</option>
                                </select>
                                @error('billing_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Rate (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="rate_per_day" class="form-control @error('rate_per_day') is-invalid @enderror" value="{{ old('rate_per_day') }}" required>
                                </div>
                                @error('rate_per_day') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Payment Terms <span class="text-danger">*</span></label>
                                <select name="payment_terms" class="form-select @error('payment_terms') is-invalid @enderror" required>
                                    <option value="weekly" {{ old('payment_terms') == 'weekly' ? 'selected' : '' }}>Weekly Billing</option>
                                    <option value="monthly" {{ old('payment_terms', 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly Billing</option>
                                    <option value="on_completion" {{ old('payment_terms') == 'on_completion' ? 'selected' : '' }}>On Completion</option>
                                </select>
                                @error('payment_terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Total Workers Required (Aggregated) <span class="text-danger">*</span></label>
                                <input type="number" name="minimum_workers_required" id="minWorkers" class="form-control" value="{{ old('minimum_workers_required', 0) }}" readonly>
                                <small class="text-muted italic">This is automatically summed from site-level requirements below.</small>
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
                                            <input class="form-check-input" type="checkbox" name="ot_enabled" value="1" id="otEnabled" {{ old('ot_enabled') ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="otEnabled">Enable OT Pay</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">OT Multiplier (e.g. 1.5x)</label>
                                        <input type="number" step="0.01" name="ot_multiplier" class="form-control form-control-sm" value="{{ old('ot_multiplier', 1.50) }}">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small fw-bold">Calculate OT after (Minutes)</label>
                                        <input type="number" name="calculate_ot_after_minutes" class="form-control form-control-sm" value="{{ old('calculate_ot_after_minutes', 0) }}">
                                        <div class="form-text x-small">Minutes worked beyond shift duration before OT starts.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 bg-light">
                            <div class="card-header bg-secondary text-white py-2">
                                <h6 class="mb-0">Site Assignments & Deployment Rules</h6>
                            </div>
                            <div class="card-body" id="sitesContainer">
                                <div class="text-center py-3 text-muted" id="sitesPlaceholder">
                                    Please select a client first to see its available sites.
                                </div>
                                <div id="sitesList" class="row"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Terms & Conditions / Special Instructions</label>
                            <textarea name="terms_and_conditions" class="form-control" rows="4">{{ old('terms_and_conditions') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.contracts.index') }}" class="btn btn-light border">Cancel & Go Back</a>
                            <button type="submit" class="btn btn-primary px-5">Create & Save Contract</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const clientsSites = @json(\App\Models\Client::with('sites')->get()->keyBy('id'));

        function loadClientSites(clientId) {
            const container = document.getElementById('sitesList');
            const placeholder = document.getElementById('sitesPlaceholder');
            container.innerHTML = '';
            
            if (!clientId || !clientsSites[clientId]) {
                placeholder.classList.remove('d-none');
                updateTotalWorkers();
                return;
            }

            const sites = clientsSites[clientId].sites;
            if (sites.length === 0) {
                placeholder.innerHTML = 'This client has no registered sites. <a href="{{ route("admin.sites.create") }}">Add one now</a>.';
                placeholder.classList.remove('d-none');
                updateTotalWorkers();
                return;
            }

            placeholder.classList.add('d-none');
            sites.forEach(site => {
                const col = document.createElement('div');
                col.className = 'col-md-6 mb-3';
                col.innerHTML = `
                    <div class="card p-3 border-secondary border-opacity-25 shadow-sm">
                        <div class="form-check mb-2">
                            <input class="form-check-input site-checkbox" type="checkbox" name="site_ids[]" value="${site.id}" id="site_${site.id}" onchange="toggleSiteInput(${site.id})">
                            <label class="form-check-label fw-bold" for="site_${site.id}">${site.site_name}</label>
                        </div>
                        <div class="site-input-group d-none" id="site_group_${site.id}">
                            <label class="small text-muted mb-1">Workers Required for this Site:</label>
                            <input type="number" name="workers_count_${site.id}" class="form-control form-control-sm worker-input" value="1" min="1" onchange="updateTotalWorkers()">
                        </div>
                    </div>
                `;
                container.appendChild(col);
            });
        }

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

        // Initialize if old input exists
        @if(old('client_id'))
            loadClientSites({{ old('client_id') }});
            // Re-check checkboxes and counts based on old input
            @foreach(old('site_ids', []) as $siteId)
                setTimeout(() => {
                    const cb = document.getElementById('site_{{ $siteId }}');
                    if(cb) {
                        cb.checked = true;
                        toggleSiteInput({{ $siteId }});
                        const countInput = document.querySelector('input[name="workers_count_{{ $siteId }}"]');
                        if(countInput) countInput.value = {{ old("workers_count_$siteId", 1) }};
                        updateTotalWorkers();
                    }
                }, 100);
            @endforeach
        @endif
    </script>
    @endpush
</x-admin-layout>
