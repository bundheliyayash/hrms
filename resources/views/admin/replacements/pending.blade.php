<x-admin-layout>
    <x-slot name="header">
        Pending Staff Substitutions
    </x-slot>

    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <i class="bi bi-lightning-fill me-2"></i> <strong>Operational Warning:</strong> Personnel changes for today's active assignments should be approved immediately to avoid site attendance gaps.
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">Service Date</th>
                            <th>Original Staff</th>
                            <th>Replacement</th>
                            <th>Site / Reason</th>
                            <th class="text-end pe-3">Direct Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($replacements as $replacement)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold">{{ $replacement->originalAssignment->assigned_date->format('d M') }}</div>
                                <small class="text-muted">{{ $replacement->originalAssignment->assigned_date->format('l') }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-danger">{{ $replacement->originalWorker->name }}</div>
                                <small class="text-muted">Absent/Emergency</small>
                            </td>
                            <td>
                                <div class="fw-bold text-success">{{ $replacement->replacementWorker->name }}</div>
                                <small class="text-muted">Subbing In</small>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $replacement->originalAssignment->site->site_name }}</div>
                                <span class="badge bg-light text-dark border small">{{ ucfirst($replacement->reason) }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex gap-2 justify-content-end">
                                    <form action="{{ route('admin.replacements.approve', $replacement) }}" method="POST">
                                        @csrf<button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Approve</button>
                                    </form>
                                    <a href="{{ route('admin.replacements.show', $replacement) }}" class="btn btn-sm btn-outline-info">Details</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-chat-square-heart display-4"></i>
                                <p class="mt-3 h5">All staff substitutions are currently up to date.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
