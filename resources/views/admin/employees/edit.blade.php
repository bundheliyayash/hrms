<x-admin-layout>
    <x-slot name="header">
        Edit Employee
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-muted">Update Employee: {{ $employee->name }}</h5>
            <div class="d-flex align-items-center">
                <span class="badge border {{ $employee->status == 'active' ? 'bg-success-subtle text-success border-success' : 'bg-danger-subtle text-danger border-danger' }} me-3">
                    {{ ucfirst($employee->status) }}
                </span>
                <form action="{{ route('admin.employees.toggle-status', $employee->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-{{ $employee->status === 'active' ? 'outline-warning' : 'outline-success' }}">
                        <i class="bi bi-{{ $employee->status === 'active' ? 'pause' : 'play' }}"></i>
                        {{ $employee->status === 'active' ? 'Deactivate Account' : 'Activate Account' }}
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.employees.update', $employee->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control" name="name" id="name" value="{{ old('name', $employee->name) }}" required>
                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control" name="email" id="email" value="{{ old('email', $employee->email) }}" required>
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label fw-bold">Password <span class="text-muted fw-normal">(Leave blank to keep current)</span></label>
                        <input type="password" class="form-control" name="password" id="password">
                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label fw-bold">Confirm Password</label>
                        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation">
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3 text-muted">Professional Details</h5>

                    <div class="col-md-4">
                        <label for="employee_id" class="form-label fw-bold">Employee ID</label>
                        <input type="text" class="form-control" name="employee_id" id="employee_id" value="{{ old('employee_id', $employee->employeeDetail->employee_id ?? '') }}" required>
                        @error('employee_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="department" class="form-label fw-bold">Department</label>
                        <input type="text" class="form-control @error('department') is-invalid @enderror" name="department" id="department" value="{{ old('department', $employee->employeeDetail->department ?? '') }}">
                        @error('department') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="designation" class="form-label fw-bold">Designation</label>
                        <input type="text" class="form-control @error('designation') is-invalid @enderror" name="designation" id="designation" value="{{ old('designation', $employee->employeeDetail->designation ?? '') }}">
                        @error('designation') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="basic_salary" class="form-label fw-bold">Basic Salary</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control @error('basic_salary') is-invalid @enderror" name="basic_salary" id="basic_salary" step="0.01" value="{{ old('basic_salary', $employee->employeeDetail->basic_salary ?? '') }}">
                        </div>
                        @error('basic_salary') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="site_id" class="form-label fw-bold">Assign Site</label>
                        <select name="site_id" id="site_id" class="form-select @error('site_id') is-invalid @enderror">
                            <option value="">-- No Site Assigned --</option>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ (old('site_id', $employee->employeeDetail->site_id ?? '')) == $site->id ? 'selected' : '' }}>
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
                                <option value="{{ $shift->id }}" {{ (old('shift_id', $employee->employeeDetail->shift_id ?? '')) == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->clock_in_time)->format('h:i A') }})
                                </option>
                            @endforeach
                        </select>
                        @error('shift_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="joining_date" class="form-label fw-bold">Joining Date</label>
                        <input type="date" class="form-control @error('joining_date') is-invalid @enderror" name="joining_date" id="joining_date" value="{{ old('joining_date', $employee->employeeDetail->joining_date ?? '') }}">
                        @error('joining_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label for="employment_type" class="form-label fw-bold">Employment Type</label>
                        <select name="employment_type" id="employment_type" class="form-select">
                            <option value="On-roll" {{ (old('employment_type', $employee->employeeDetail->employment_type ?? '')) == 'On-roll' ? 'selected' : '' }}>On-roll</option>
                            <option value="Temporary" {{ (old('employment_type', $employee->employeeDetail->employment_type ?? '')) == 'Temporary' ? 'selected' : '' }}>Temporary</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="role_id" class="form-label fw-bold text-primary">System Role</label>
                        <select name="role_id" id="role_id" class="form-select border-primary @error('role_id') is-invalid @enderror" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ (old('role_id', $employee->role_id)) == $role->id ? 'selected' : '' }}>
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="phone" class="form-label fw-bold">Phone Number</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone', $employee->employeeDetail->phone ?? '') }}">
                        @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-4">
                    
                    <h5 class="mb-3 text-muted">Compliance & Financials</h5>

                    <div class="col-md-4">
                        <label for="bank_name" class="form-label fw-bold">Bank Name</label>
                        <input type="text" class="form-control" name="bank_name" id="bank_name" value="{{ old('bank_name', $employee->employeeDetail->bank_name ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="account_number" class="form-label fw-bold">Account Number</label>
                        <input type="text" class="form-control" name="account_number" id="account_number" value="{{ old('account_number', $employee->employeeDetail->account_number ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="ifsc_code" class="form-label fw-bold">IFSC Code</label>
                        <input type="text" class="form-control" name="ifsc_code" id="ifsc_code" value="{{ old('ifsc_code', $employee->employeeDetail->ifsc_code ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="aadhar_number" class="form-label fw-bold">Aadhar Number</label>
                        <input type="text" class="form-control" name="aadhar_number" id="aadhar_number" value="{{ old('aadhar_number', $employee->employeeDetail->aadhar_number ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="pan_number" class="form-label fw-bold">PAN Number</label>
                        <input type="text" class="form-control" name="pan_number" id="pan_number" value="{{ old('pan_number', $employee->employeeDetail->pan_number ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="emergency_contact_name" class="form-label fw-bold">Emergency Contact</label>
                        <input type="text" class="form-control" name="emergency_contact_name" id="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->employeeDetail->emergency_contact_name ?? '') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="emergency_contact_phone" class="form-label fw-bold">Emergency Phone</label>
                        <input type="text" class="form-control" name="emergency_contact_phone" id="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->employeeDetail->emergency_contact_phone ?? '') }}">
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label fw-bold">Address</label>
                        <textarea class="form-control" name="address" id="address" rows="3">{{ old('address', $employee->employeeDetail->address ?? '') }}</textarea>
                    </div>

                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
