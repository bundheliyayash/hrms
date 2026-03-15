<x-admin-layout>
    <x-slot name="header">
        Edit Client
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-muted">Update Client: {{ $client->name }}</h5>
            <div class="d-flex align-items-center">
                @if($client->is_active)
                    <span class="badge border bg-success-subtle text-success border-success me-3">Active</span>
                @else
                    <span class="badge border bg-danger-subtle text-danger border-danger me-3">Inactive</span>
                @endif
                <form action="{{ route('admin.clients.toggle-status', $client->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-{{ $client->is_active ? 'outline-warning' : 'outline-success' }}">
                        <i class="bi bi-{{ $client->is_active ? 'pause' : 'play' }}"></i>
                        {{ $client->is_active ? 'Deactivate Client' : 'Activate Client' }}
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.clients.update', $client->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">Client Name</label>
                        <input type="text" class="form-control" name="name" id="name" value="{{ $client->name }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control" name="email" id="email" value="{{ $client->email }}">
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-bold">Phone</label>
                        <input type="text" class="form-control" name="phone" id="phone" value="{{ $client->phone }}">
                    </div>

                    <div class="col-md-6">
                        <label for="service_start_date" class="form-label fw-bold">Service Start Date</label>
                        <input type="date" class="form-control" name="service_start_date" id="service_start_date" value="{{ $client->service_start_date }}">
                    </div>

                    <div class="col-md-6">
                        <label for="service_end_date" class="form-label fw-bold">Service End Date</label>
                        <input type="date" class="form-control" name="service_end_date" id="service_end_date" value="{{ $client->service_end_date }}">
                        <small class="text-muted">Leave blank for open-ended service</small>
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label fw-bold">Address</label>
                        <textarea class="form-control" name="address" id="address" rows="3">{{ $client->address }}</textarea>
                    </div>
                </div>

                <!-- Client Portal Access -->
                <div class="mt-4 pt-4 border-top">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-key-fill text-primary me-2"></i>Client Portal Access
                    </h6>
                    <p class="text-muted small">Allow this client to log into the portal and submit employee attendance for their sites.</p>

                    @if($client->user)
                        <div class="alert alert-success py-2 small">
                            <i class="bi bi-check-circle me-1"></i> 
                            Portal account active: <strong>{{ $client->user->email }}</strong>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="portal_email" class="form-label fw-bold">Portal Login Email</label>
                            <input type="email" class="form-control @error('portal_email') is-invalid @enderror" 
                                   name="portal_email" id="portal_email" 
                                   value="{{ old('portal_email', $client->user->email ?? '') }}"
                                   placeholder="client@example.com">
                            @error('portal_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave blank to skip portal access.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="portal_password" class="form-label fw-bold">
                                {{ $client->user ? 'New Password (leave blank to keep)' : 'Portal Password' }}
                            </label>
                            <input type="password" class="form-control @error('portal_password') is-invalid @enderror" 
                                   name="portal_password" id="portal_password" 
                                   placeholder="{{ $client->user ? '••••••••' : 'Min 6 characters' }}">
                            @error('portal_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Client</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
