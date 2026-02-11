<x-admin-layout>
    <x-slot name="header">
        Bulk Assign Staff
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-muted">Import Assignments from CSV</h5>
                    <a href="{{ route('admin.assignments.template') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-download me-1"></i> Download Template
                    </a>
                </div>
                <div class="card-body p-4">
                    
                    @if(session('error'))
                        <div class="alert alert-danger mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('import_errors'))
                        <div class="alert alert-warning mb-4">
                            <h6 class="fw-bold">Validation Errors:</h6>
                            <ul class="mb-0 small">
                                @foreach(session('import_errors') as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.assignments.import.post') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="csv_file" class="form-label fw-bold">Select CSV File</label>
                            <input type="file" class="form-control" name="csv_file" id="csv_file" required>
                            <div class="form-text small mt-2">
                                <i class="bi bi-info-circle me-1"></i> File must be a valid CSV matching our template structure.
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded mb-4">
                            <h6 class="fw-bold small mb-2 text-uppercase">Instructions:</h6>
                            <ul class="small text-muted mb-0">
                                <li><strong>employee_id</strong>: Should match the internal Employee ID (e.g., EMP001).</li>
                                <li><strong>site_id</strong> & <strong>contract_id</strong>: Numeric IDs from the system.</li>
                                <li><strong>date</strong>: Format YYYY-MM-DD.</li>
                                <li><strong>shift_id</strong>: Numeric ID from Shift settings.</li>
                            </ul>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary py-2 fw-bold">
                                <i class="bi bi-cloud-upload me-1"></i> Start Import Process
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
