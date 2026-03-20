<x-client-portal-layout>
    <style>
        .prof-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; }
        .prof-header { background:linear-gradient(135deg,#0f4c75 0%,#3282b8 100%); color:#fff; padding:.9rem 1.25rem; }
    </style>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-person-circle me-2 text-primary"></i>My Profile</h4>
            <p class="text-muted small mb-0">View your account details and change your password</p>
        </div>
        <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 mb-3"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger py-2 mb-3">
            @foreach($errors->all() as $e)<div class="small"><i class="bi bi-exclamation-triangle me-1"></i>{{ $e }}</div>@endforeach
        </div>
    @endif

    <div class="row g-4">
        {{-- Account Info --}}
        <div class="col-md-6">
            <div class="prof-card">
                <div class="prof-header">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-building me-2"></i>Account Information</h6>
                </div>
                <div class="p-4">
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Company / Client Name</div>
                        <div class="fw-bold">{{ $client->name }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Contact Person</div>
                        <div class="fw-bold">{{ $client->contact_person ?? '—' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Email (Login)</div>
                        <div class="fw-bold">{{ $user->email }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Phone</div>
                        <div class="fw-bold">{{ $client->phone ?? '—' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Address</div>
                        <div class="fw-bold">{{ $client->address ?? '—' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Service Start</div>
                        <div class="fw-bold">{{ $client->service_start_date ? \Carbon\Carbon::parse($client->service_start_date)->format('d M Y') : '—' }}</div>
                    </div>
                    <div>
                        <div class="text-muted small mb-1">Service Status</div>
                        @if($client->isServiceActive())
                            <span class="badge bg-success-subtle text-success border">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="col-md-6">
            <div class="prof-card">
                <div class="prof-header">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-key me-2"></i>Change Password</h6>
                </div>
                <div class="p-4">
                    <form method="POST" action="{{ route('client.profile.password') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Current Password</label>
                            <input type="password" name="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror"
                                   placeholder="Enter your current password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">New Password</label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimum 8 characters" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small">Confirm New Password</label>
                            <input type="password" name="password_confirmation"
                                   class="form-control"
                                   placeholder="Repeat new password" required>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 w-100">
                            <i class="bi bi-check-circle me-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>

            {{-- Sites summary --}}
            <div class="prof-card mt-4">
                <div class="prof-header" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-geo-alt me-2"></i>My Sites ({{ $client->sites->count() }})</h6>
                </div>
                <div class="p-3">
                    @forelse($client->sites as $site)
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div>
                                <div class="small fw-bold">{{ $site->site_name }}</div>
                                <div class="text-muted" style="font-size:.7rem;">{{ $site->address ?? 'No address' }}</div>
                            </div>
                            @if($site->is_active)
                                <span class="badge bg-success-subtle text-success border" style="font-size:.65rem;">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.65rem;">Inactive</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No sites assigned.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-client-portal-layout>
