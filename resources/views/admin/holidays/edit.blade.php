<x-admin-layout>
    <x-slot name="header">
        Edit Holiday: {{ $holiday->name }}
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.holidays.update', $holiday->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label fw-bold small text-muted">Holiday Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $holiday->name) }}" required>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="start_date" class="form-label fw-bold small text-muted">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $holiday->start_date->format('Y-m-d')) }}" required>
                                @error('start_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="end_date" class="form-label fw-bold small text-muted">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', $holiday->end_date ? $holiday->end_date->format('Y-m-d') : '') }}">
                                @error('end_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="type" class="form-label fw-bold small text-muted">Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="paid" {{ old('type', $holiday->type) == 'paid' ? 'selected' : '' }}>Paid Holiday</option>
                                    <option value="unpaid" {{ old('type', $holiday->type) == 'unpaid' ? 'selected' : '' }}>Unpaid Holiday</option>
                                    <option value="optional" {{ old('type', $holiday->type) == 'optional' ? 'selected' : '' }}>Optional</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="applicable_type" class="form-label fw-bold small text-muted">Applicable To <span class="text-danger">*</span></label>
                                <select name="applicable_type" id="applicable_type" class="form-select" required onchange="toggleApplicableFields(this.value)">
                                    <option value="all" {{ old('applicable_type', $holiday->applicable_type) == 'all' ? 'selected' : '' }}>All Employees</option>
                                    <option value="department" {{ old('applicable_type', $holiday->applicable_type) == 'department' ? 'selected' : '' }}>Specific Departments</option>
                                    <option value="site" {{ old('applicable_type', $holiday->applicable_type) == 'site' ? 'selected' : '' }}>Specific Sites</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="status" class="form-label fw-bold small text-muted">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="active" {{ old('status', $holiday->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $holiday->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <!-- Conditional Departments -->
                            @php
                                $selectedDepts = old('departments', $holiday->departments->pluck('department')->toArray());
                            @endphp
                            <div class="col-12" id="dept_div" style="display: {{ old('applicable_type', $holiday->applicable_type) == 'department' ? 'block' : 'none' }};">
                                <label class="form-label fw-bold small text-muted">Select Departments <span class="text-danger">*</span></label>
                                <div class="row">
                                    @foreach($departments as $dept)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="departments[]" value="{{ $dept }}" id="dept_{{ $loop->index }}" {{ in_array($dept, $selectedDepts) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="dept_{{ $loop->index }}">{{ $dept }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('departments') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Conditional Sites -->
                            @php
                                $selectedSites = old('sites', $holiday->sites->pluck('id')->toArray());
                            @endphp
                            <div class="col-12" id="site_div" style="display: {{ old('applicable_type', $holiday->applicable_type) == 'site' ? 'block' : 'none' }};">
                                <label class="form-label fw-bold small text-muted">Select Sites <span class="text-danger">*</span></label>
                                <div class="row">
                                    @foreach($sites as $site)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="sites[]" value="{{ $site->id }}" id="site_{{ $site->id }}" {{ in_array($site->id, $selectedSites) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="site_{{ $site->id }}">{{ $site->site_name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('sites') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label fw-bold small text-muted">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $holiday->description) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top text-end">
                            <a href="{{ route('admin.holidays.index') }}" class="btn btn-light btn-sm px-4 me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-sm px-4">Update Holiday</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleApplicableFields(value) {
            document.getElementById('dept_div').style.display = value === 'department' ? 'block' : 'none';
            document.getElementById('site_div').style.display = value === 'site' ? 'block' : 'none';
        }
    </script>
    @endpush
</x-admin-layout>
