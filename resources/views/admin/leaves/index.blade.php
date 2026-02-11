<x-admin-layout>
    <x-slot name="header">
        Leave Management
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted">All Leave Applications</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Employee</th>
                            <th>Type</th>
                            <th>Duration</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="text-end px-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaves as $leave)
                        <tr>
                            <td class="px-3">
                                <div class="fw-bold">{{ $leave->user->name }}</div>
                                <div class="text-muted small">{{ $leave->user->employeeDetail->employee_id ?? '' }}</div>
                            </td>
                            <td>{{ $leave->leave_type }}</td>
                            <td>
                                <div>{{ $leave->start_date }}</div>
                                <small class="text-muted">to {{ $leave->end_date }}</small>
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($leave->reason, 30) }}</td>
                            <td>
                                <span class="badge {{ $leave->status == 'approved' ? 'bg-success' : ($leave->status == 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                    {{ ucfirst($leave->status) }}
                                </span>
                            </td>
                            <td class="text-end px-3">
                                @if($leave->status === 'pending')
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#processLeave{{ $leave->id }}">
                                        Process
                                    </button>

                                    <!-- Process Modal -->
                                    <div class="modal fade" id="processLeave{{ $leave->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form action="{{ route('admin.leaves.update', $leave->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-content border-0 shadow text-start">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Process Leave Request</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted mb-4">You are processing leave for <strong>{{ $leave->user->name }}</strong>.</p>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">Decision</label>
                                                            <div class="d-flex gap-3">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="status" id="approve{{ $leave->id }}" value="approved" checked>
                                                                    <label class="form-check-label text-success fw-bold" for="approve{{ $leave->id }}">Approve</label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="status" id="reject{{ $leave->id }}" value="rejected">
                                                                    <label class="form-check-label text-danger fw-bold" for="reject{{ $leave->id }}">Reject</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">Admin Comment (Optional)</label>
                                                            <textarea name="admin_comment" class="form-control" rows="3" placeholder="Provide feedback to employee..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4">Submit Decision</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <div class="small text-muted">
                                        <div><i class="bi bi-person-check me-1"></i> Processed</div>
                                        @if($leave->admin_comment)
                                            <div class="x-small fw-italic text-truncate" style="max-width: 150px;" title="{{ $leave->admin_comment }}">{{ $leave->admin_comment }}</div>
                                        @endif
                                    </div>
                                @endif
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
