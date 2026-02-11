<x-admin-layout>
    <x-slot name="header">
        Create Worker Substitution
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 text-muted">Emergency Staff Substitution</h5>
                </div>
                <div class="card-body py-4">
                    <form action="{{ route('admin.replacements.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Step 1: Select Original Assignment <span class="text-danger">*</span></label>
                            <select name="original_assignment_id" id="assignmentSelect" class="form-select @error('original_assignment_id') is-invalid @enderror" required onchange="loadWorkerInfo()">
                                <option value="">Select an active assignment...</option>
                                @foreach($assignments as $assignment)
                                    <option value="{{ $assignment->id }}" 
                                            data-worker-name="{{ $assignment->employee->name }}"
                                            data-site-name="{{ $assignment->site->site_name }}"
                                            data-date="{{ $assignment->assigned_date->format('d M, Y') }}"
                                            {{ request('assignment_id') == $assignment->id ? 'selected' : '' }}>
                                        [{{ $assignment->assigned_date->format('d/m') }}] {{ $assignment->employee->name }} -> {{ $assignment->site->site_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Only showing future or today's assignments that haven't been completed.</small>
                            @error('original_assignment_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div id="workerInfoCard" class="card bg-light border-0 mb-4 d-none">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="text-muted small">Replacing:</div>
                                        <div class="fw-bold text-danger" id="infoWorker">-</div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <div class="text-muted small">At Site:</div>
                                        <div class="fw-bold" id="infoSite">-</div>
                                        <div class="text-muted small" id="infoDate">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Step 2: Assign Replacement Worker <span class="text-danger">*</span></label>
                            <select name="replacement_worker_id" class="form-select @error('replacement_worker_id') is-invalid @enderror" required>
                                <option value="">Choose substitute...</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} (Emp ID: {{ $employee->employeeDetail->employee_id ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                            @error('replacement_worker_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Replacement Reason <span class="text-danger">*</span></label>
                                <select name="reason" class="form-select" required>
                                    <option value="absent">Absent without Notice</option>
                                    <option value="leave">Planned Leave</option>
                                    <option value="emergency">Family/Personal Emergency</option>
                                    <option value="client_request">Client Requested Change</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Is Emergency?</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_emergency" value="1" id="isEmergency" checked>
                                    <label class="form-check-label" for="isEmergency">Emergency Dispatch</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Administrative Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Explain the rationale for this change..."></textarea>
                        </div>

                        <div class="d-grid pt-2">
                            <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold">PROPOSE SUBSTITUTION</button>
                            <a href="{{ route('admin.replacements.index') }}" class="btn btn-link text-muted mt-2 text-decoration-none">Back to Logs</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function loadWorkerInfo() {
            const select = document.getElementById('assignmentSelect');
            const card = document.getElementById('workerInfoCard');
            const selected = select.options[select.selectedIndex];
            
            if (select.value === "") {
                card.classList.add('d-none');
                return;
            }

            card.classList.remove('d-none');
            document.getElementById('infoWorker').innerText = selected.dataset.workerName;
            document.getElementById('infoSite').innerText = selected.dataset.siteName;
            document.getElementById('infoDate').innerText = selected.dataset.date;
        }

        @if(request('assignment_id'))
            loadWorkerInfo();
        @endif
    </script>
    @endpush
</x-admin-layout>
