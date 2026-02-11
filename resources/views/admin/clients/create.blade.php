<x-admin-layout>
    <x-slot name="header">
        Add New Client
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted">Client Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.clients.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">Client Name</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control" name="email" id="email">
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-bold">Phone</label>
                        <input type="text" class="form-control" name="phone" id="phone">
                    </div>

                    <div class="col-md-6">
                        <label for="service_start_date" class="form-label fw-bold">Service Start Date</label>
                        <input type="date" class="form-control" name="service_start_date" id="service_start_date" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="service_end_date" class="form-label fw-bold">Service End Date</label>
                        <input type="date" class="form-control" name="service_end_date" id="service_end_date">
                        <small class="text-muted">Leave blank for open-ended service</small>
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label fw-bold">Address</label>
                        <textarea class="form-control" name="address" id="address" rows="3"></textarea>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Client</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
