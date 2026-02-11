<x-admin-layout>
    <x-slot name="header">
        My Profile
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Profile Photo Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="position-relative d-inline-block mb-3">
                        @if(auth()->user()->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}" 
                                 class="rounded-circle" 
                                 style="width: 150px; height: 150px; object-fit: cover;" 
                                 alt="Profile Photo">
                        @else
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" 
                                 style="width: 150px; height: 150px;">
                                <i class="bi bi-person-circle text-primary" style="font-size: 80px;"></i>
                            </div>
                        @endif
                        @if($editableFields['profile_photo'])
                        <button class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0" 
                                data-bs-toggle="modal" 
                                data-bs-target="#photoModal"
                                style="width: 40px; height: 40px;">
                            <i class="bi bi-camera"></i>
                        </button>
                        @endif
                    </div>
                    <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
                    <p class="text-muted small mb-2">{{ $employee->designation ?? 'Employee' }}</p>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">
                        <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Active
                    </span>
                </div>
            </div>

            <!-- Quick Info Card -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Quick Information</h6>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                            <i class="bi bi-envelope text-primary"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Email</small>
                            <span class="small">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success bg-opacity-10 rounded p-2 me-3">
                            <i class="bi bi-telephone text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Phone</small>
                            <span class="small">{{ $employee->phone ?? 'Not provided' }}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded p-2 me-3">
                            <i class="bi bi-geo-alt text-warning"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Location</small>
                            <span class="small">{{ $employee->site->site_name ?? 'Not assigned' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Details Card -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold">Personal Information</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('employee.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Full Name (Read Only) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Full Name</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                                <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Admin controlled</small>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Email Address</label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', auth()->user()->email) }}"
                                       {{ $editableFields['email'] ? '' : 'disabled' }}>
                                @if(!$editableFields['email'])
                                    <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Admin controlled</small>
                                @endif
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Phone Number</label>
                                <input type="text" 
                                       name="phone" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $employee->phone ?? '') }}"
                                       {{ $editableFields['phone'] ? '' : 'disabled' }}>
                                @if(!$editableFields['phone'])
                                    <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Admin controlled</small>
                                @endif
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Date of Birth -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Date of Birth</label>
                                <input type="date" 
                                       name="date_of_birth" 
                                       class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       value="{{ old('date_of_birth', $employee->date_of_birth ?? '') }}"
                                       {{ $editableFields['date_of_birth'] ? '' : 'disabled' }}>
                                @if(!$editableFields['date_of_birth'])
                                    <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Admin controlled</small>
                                @endif
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Gender -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Gender</label>
                                <select name="gender" 
                                        class="form-select @error('gender') is-invalid @enderror"
                                        {{ $editableFields['gender'] ? '' : 'disabled' }}>
                                    <option value="">Select gender</option>
                                    <option value="Male" {{ old('gender', $employee->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender', $employee->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('gender', $employee->gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @if(!$editableFields['gender'])
                                    <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Admin controlled</small>
                                @endif
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Blood Group -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Blood Group</label>
                                <select name="blood_group" 
                                        class="form-select @error('blood_group') is-invalid @enderror"
                                        {{ $editableFields['blood_group'] ? '' : 'disabled' }}>
                                    <option value="">Select blood group</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                        <option value="{{ $bg }}" {{ old('blood_group', $employee->blood_group ?? '') == $bg ? 'selected' : '' }}>
                                            {{ $bg }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(!$editableFields['blood_group'])
                                    <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Admin controlled</small>
                                @endif
                                @error('blood_group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="col-12">
                                <label class="form-label fw-bold small">Address</label>
                                <textarea name="address" 
                                          class="form-control @error('address') is-invalid @enderror" 
                                          rows="3"
                                          {{ $editableFields['address'] ? '' : 'disabled' }}>{{ old('address', $employee->address ?? '') }}</textarea>
                                @if(!$editableFields['address'])
                                    <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Admin controlled</small>
                                @endif
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Emergency Contact -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Emergency Contact</label>
                                <input type="text" 
                                       name="emergency_contact" 
                                       class="form-control @error('emergency_contact') is-invalid @enderror" 
                                       value="{{ old('emergency_contact', $employee->emergency_contact ?? '') }}"
                                       {{ $editableFields['emergency_contact'] ? '' : 'disabled' }}>
                                @if(!$editableFields['emergency_contact'])
                                    <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Admin controlled</small>
                                @endif
                                @error('emergency_contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Department (Read Only) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Department</label>
                                <input type="text" class="form-control" value="{{ $employee->department ?? 'Not assigned' }}" disabled>
                                <small class="text-muted"><i class="bi bi-lock-fill me-1"></i>Admin controlled</small>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Upload Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Update Profile Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('employee.profile.updatePhoto') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Choose Photo</label>
                            <input type="file" name="profile_photo" class="form-control" accept="image/*" required>
                            <small class="text-muted">Maximum file size: 2MB. Accepted formats: JPG, PNG</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-upload me-2"></i>Upload Photo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
