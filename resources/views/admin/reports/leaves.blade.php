<x-admin-layout>
    <x-slot name="header">
        Leave Report
    </x-slot>

    <div class="mb-3">
        <a href="{{ route('admin.reports.index') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Summary Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-white shadow-sm border-start border-4 border-primary">
                <div class="card-body text-center">
                    <div class="text-muted small">Total Applications</div>
                    <div class="h3 fw-bold mb-0 text-primary">{{ $totalLeaves }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white shadow-sm border-start border-4 border-success">
                <div class="card-body text-center">
                    <div class="text-muted small">Approved</div>
                    <div class="h3 fw-bold mb-0 text-success">{{ $approvedLeaves }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white shadow-sm border-start border-4 border-warning">
                <div class="card-body text-center">
                    <div class="text-muted small">Pending</div>
                    <div class="h3 fw-bold mb-0 text-warning">{{ $pendingLeaves }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white shadow-sm border-start border-4 border-danger">
                <div class="card-body text-center">
                    <div class="text-muted small">Rejected</div>
                    <div class="h3 fw-bold mb-0 text-danger">{{ $rejectedLeaves }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted">Detailed Leave History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Employee</th>
                            <th>Type</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaves as $leave)
                        <tr>
                            <td class="px-3">
                                <b>{{ $leave->user->name }}</b>
                            </td>
                            <td>{{ $leave->leave_type }}</td>
                            <td>{{ $leave->start_date }} <span class="text-muted">to</span> {{ $leave->end_date }}</td>
                            <td>
                                <span class="badge {{ $leave->status == 'approved' ? 'bg-success' : ($leave->status == 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                    {{ ucfirst($leave->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $leaves->links() }}
        </div>
    </div>
</x-admin-layout>
