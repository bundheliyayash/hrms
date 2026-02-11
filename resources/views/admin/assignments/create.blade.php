<x-admin-layout>
    <x-slot name="header">
        Assign Workers to Sites
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-muted">Worker Deployment Plan</h5>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="assign_mode" id="singleMode" checked onchange="toggleMode('single')">
                        <label class="btn btn-outline-primary btn-sm" for="singleMode">Single Assignment</label>
                        
                        <input type="radio" class="btn-check" name="assign_mode" id="bulkMode" onchange="toggleMode('bulk')">
                        <label class="btn btn-outline-primary btn-sm" for="bulkMode">Bulk Deployment</label>
                    </div>
                </div>
                <div class="card-body py-4">
                    <!-- Single Assignment Form -->
                    <form action="{{ route('admin.assignments.store') }}" method="POST" id="singleForm">
                        @csrf
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Target Site <span class="text-danger">*</span></label>
                                <select name="site_id" class="form-select @error('site_id') is-invalid @enderror" required>
                                    <option value="">Select a Site...</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->site_name }} ({{ $site->location }})</option>
                                    @endforeach
                                </select>
                                @error('site_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Employee <span class="text-danger">*</span></label>
                                <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">Choose Employee...</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employeeDetail->employee_id ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted italic">Only showing active employees with 'employee' role.</small>
                                @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Assignment Date <span class="text-danger">*</span></label>
                                <input type="date" name="assigned_date" class="form-control @error('assigned_date') is-invalid @enderror" value="{{ old('assigned_date', date('Y-m-d')) }}" required>
                                @error('assigned_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Shift Schedule</label>
                                <select name="shift_id" class="form-select">
                                    <option value="">Default/Standard Shift</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Deployment Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Any specific instructions for this shift..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.assignments.index') }}" class="btn btn-light border">Nevermind</a>
                            <button type="submit" class="btn btn-primary px-5">Assign Worker</button>
                        </div>
                    </form>

                    <!-- Bulk Assignment Form (Initially Hidden) -->
                    <form action="{{ route('admin.assignments.bulk-assign') }}" method="POST" id="bulkForm" class="d-none">
                        @csrf
                        <div class="row g-4 mb-4 text-primary bg-light p-3 rounded mx-0">
                            <div class="col-12"><i class="bi bi-info-circle me-2"></i> Bulk mode creates multiple shifts for a range of workers and dates. Conflicting existing shifts will be automatically skipped.</div>
                        </div>

                        <div class="row g-4 mb-4 text-start">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Target Site <span class="text-danger">*</span></label>
                                <select name="site_id" class="form-select" required>
                                    <option value="">Select a Site...</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->site_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-4 text-start">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d', strtotime('+6 days')) }}" required>
                            </div>
                        </div>

                        <div class="mb-4 text-start">
                            <label class="form-label fw-bold">Weekly Schedule (Select Days) <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3 p-3 border rounded bg-white">
                                @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="{{ $day }}" id="day_{{ $day }}" checked>
                                    <label class="form-check-label px-1" for="day_{{ $day }}">{{ $day }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="row g-4 mb-4 text-start">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Select Workers (Multi-select) <span class="text-danger">*</span></label>
                                <select name="user_ids[]" class="form-select @error('user_ids') is-invalid @enderror" multiple size="6" required>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }} (Emp ID: {{ $employee->employeeDetail->employee_id ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hold Ctrl (CMD) to select multiple workers.</small>
                            </div>
                        </div>

                        <div class="row g-4 mb-4 text-start">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Common Shift Schedule</label>
                                <select name="shift_id" class="form-select">
                                    <option value="">Default/Standard Shift</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.assignments.index') }}" class="btn btn-light border">Back to List</a>
                            <button type="submit" class="btn btn-info text-white px-5">Bulk Deployment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleMode(mode) {
            const single = document.getElementById('singleForm');
            const bulk = document.getElementById('bulkForm');
            
            if (mode === 'single') {
                single.classList.remove('d-none');
                bulk.classList.add('d-none');
            } else {
                single.classList.add('d-none');
                bulk.classList.remove('d-none');
            }
        }
    </script>
    @endpush
</x-admin-layout>
