<x-admin-layout>
    <x-slot name="header">
        Substitution Request Details
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-muted">Request Log #{{ $replacement->id }}</h5>
                    <span class="badge {{ $replacement->status == 'approved' ? 'bg-success' : ($replacement->status == 'pending' ? 'bg-warning' : 'bg-danger') }} fs-6 px-3">
                        {{ strtoupper($replacement->status) }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-5 p-4 text-center">
                            <div class="text-muted small mb-1">ORIGINAL WORKER</div>
                            <h5 class="fw-bold text-danger mb-0">{{ $replacement->originalWorker->name }}</h5>
                            <small class="text-muted">Emp ID: {{ $replacement->originalWorker->employeeDetail->employee_id ?? 'N/A' }}</small>
                            <hr class="my-3">
                            <div class="badge bg-light text-dark border">Reason: {{ ucfirst($replacement->reason) }}</div>
                        </div>
                        <div class="col-md-2 p-4 text-center d-flex align-items-center justify-content-center">
                            <i class="bi bi-arrow-right-circle-fill text-primary display-4"></i>
                        </div>
                        <div class="col-md-5 p-4 text-center">
                            <div class="text-muted small mb-1">REPLACEMENT WORKER</div>
                            <h5 class="fw-bold text-success mb-0">{{ $replacement->replacementWorker->name }}</h5>
                            <small class="text-muted">Emp ID: {{ $replacement->replacementWorker->employeeDetail->employee_id ?? 'N/A' }}</small>
                            <hr class="my-3">
                            <div class="badge bg-light text-dark border">Type: Substitution</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 py-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-muted small">Target Site:</div>
                            <div class="fw-bold">{{ $replacement->originalAssignment->site->site_name }}</div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="text-muted small">Service Date:</div>
                            <div class="fw-bold">{{ $replacement->originalAssignment->assigned_date->format('d M, Y') }}</div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="text-muted small">Requested At:</div>
                            <div class="fw-bold">{{ $replacement->created_at->format('d/m/y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-chat-left-text me-2"></i>Admin Notes / Justification</h6>
                    <blockquote class="blockquote bg-light p-3 rounded small">
                        {{ $replacement->notes ?? 'No additional notes provided for this substitution.' }}
                    </blockquote>
                </div>
            </div>

            @if($replacement->status == 'pending')
            <div class="card shadow-sm border-0 bg-light border-start border-warning border-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 fw-bold">Action Required: Approval/Rejection</h6>
                        <p class="text-muted small mb-0">Review the staff change and confirm if it adheres to site policies.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.replacements.reject', $replacement) }}" method="POST">
                            @csrf<button type="submit" class="btn btn-outline-danger">Reject</button>
                        </form>
                        <form action="{{ route('admin.replacements.approve', $replacement) }}" method="POST">
                            @csrf<button type="submit" class="btn btn-success px-4">Approve Staff Change</button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            <div class="text-center mt-4">
                <a href="{{ route('admin.replacements.index') }}" class="btn btn-link text-muted"><i class="bi bi-arrow-left"></i> Back to Logs</a>
            </div>
        </div>
    </div>
</x-admin-layout>
