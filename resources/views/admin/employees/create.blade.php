<x-admin-layout>
    <x-slot name="header">
        Add New Employee
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted">Employee Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.employees.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" required>
                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" required>
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label fw-bold">Password</label>
                        <input type="password" class="form-control" name="password" id="password" required>
                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label fw-bold">Confirm Password</label>
                        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" required>
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3 text-muted">Professional Details</h5>

                    <div class="col-md-4">
                        <label for="employee_id" class="form-label fw-bold">Employee ID</label>
                        <input type="text" class="form-control" name="employee_id" id="employee_id" value="{{ old('employee_id') }}" required>
                        @error('employee_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="department" class="form-label fw-bold">Department</label>
                        <input type="text" class="form-control @error('department') is-invalid @enderror" name="department" id="department" value="{{ old('department') }}">
                        @error('department') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="designation" class="form-label fw-bold">Designation</label>
                        <input type="text" class="form-control @error('designation') is-invalid @enderror" name="designation" id="designation" value="{{ old('designation') }}">
                        @error('designation') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="basic_salary" class="form-label fw-bold">Basic Salary</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control @error('basic_salary') is-invalid @enderror" name="basic_salary" id="basic_salary" step="0.01" value="{{ old('basic_salary') }}">
                        </div>
                        @error('basic_salary') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="site_id" class="form-label fw-bold">Assign Site</label>
                        <select name="site_id" id="site_id" class="form-select @error('site_id') is-invalid @enderror">
                            <option value="">-- No Site Assigned --</option>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>
                                    {{ $site->site_name }} ({{ optional($site->client)->name ?? 'Archived Client' }})
                                </option>
                            @endforeach
                        </select>
                        @error('site_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="shift_id" class="form-label fw-bold">Assign Shift</label>
                        <select name="shift_id" id="shift_id" class="form-select @error('shift_id') is-invalid @enderror">
                            <option value="">-- No Shift Assigned --</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->clock_in_time)->format('h:i A') }})
                                </option>
                            @endforeach
                        </select>
                        @error('shift_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="joining_date" class="form-label fw-bold">Joining Date</label>
                        <input type="date" class="form-control @error('joining_date') is-invalid @enderror" name="joining_date" id="joining_date" value="{{ old('joining_date') }}">
                        @error('joining_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="employment_type" class="form-label fw-bold">Employment Type</label>
                        <select name="employment_type" id="employment_type" class="form-select">
                            <option value="On-roll" {{ old('employment_type') == 'On-roll' ? 'selected' : '' }}>On-roll</option>
                            <option value="Temporary" {{ old('employment_type') == 'Temporary' ? 'selected' : '' }}>Temporary</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="role_id" class="form-label fw-bold text-primary">System Role</label>
                        <select name="role_id" id="role_id" class="form-select border-primary" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : ($role->name === 'employee' ? 'selected' : '') }}>
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="phone" class="form-label fw-bold">Phone Number</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone') }}">
                        @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label fw-bold">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="address" rows="3">{{ old('address') }}</textarea>
                        @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Employee</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
