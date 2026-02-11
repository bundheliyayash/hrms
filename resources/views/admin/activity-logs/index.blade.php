<x-admin-layout>
    <x-slot name="header">
        System Activity Logs
    </x-slot>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted"><i class="bi bi-clock-history me-2"></i> Audit Trail</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Subject</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="px-4 text-nowrap text-muted small">
                                {{ $log->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td>
                                @if($log->causer)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            {{ substr($log->causer->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark small d-block">{{ $log->causer->name }}</span>
                                            <span class="badge bg-secondary py-0 px-1" style="font-size: 0.7rem;">{{ strtoupper($log->causer->role) }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">System</span>
                                @endif
                            </td>
                            <td>
                                @if($log->description == 'created')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">CREATED</span>
                                @elseif($log->description == 'updated')
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">UPDATED</span>
                                @elseif($log->description == 'deleted')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">DELETED</span>
                                @else
                                    <span class="badge bg-secondary">{{ strtoupper($log->description) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border p-2">
                                    <i class="bi bi-box-seam me-1"></i> {{ class_basename($log->subject_type) }}
                                </span>
                                @if($log->subject && isset($log->subject->user))
                                    <div class="mt-1">
                                        <small class="text-muted">For:</small>
                                        <span class="text-primary fw-600 small">{{ $log->subject->user->name }}</span>
                                    </div>
                                @elseif($log->subject && class_basename($log->subject_type) == 'User')
                                    <div class="mt-1">
                                        <small class="text-muted">Target:</small>
                                        <span class="text-primary fw-600 small">{{ $log->subject->name }}</span>
                                    </div>
                                @endif
                            </td>
                            <td style="min-width: 250px;">
                                @php $changes = $log->changes; @endphp
                                @if(count($changes) > 0)
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2 small" type="button" data-bs-toggle="collapse" data-bs-target="#log-details-{{ $log->id }}">
                                        {{ count($changes) }} Changes
                                    </button>
                                    <div class="collapse mt-2" id="log-details-{{ $log->id }}">
                                        <div class="table-sm table-responsive border rounded bg-light">
                                            <table class="table table-sm table-borderless mb-0 x-small">
                                                <thead class="border-bottom">
                                                    <tr>
                                                        <th class="py-1">Field</th>
                                                        <th class="py-1">Old</th>
                                                        <th class="py-1">New</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($changes as $change)
                                                        <tr>
                                                            <td class="fw-bold text-muted py-1">{{ $change['field'] }}</td>
                                                            <td class="text-danger py-1" style="word-break: break-all;">
                                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill fw-normal">
                                                                    {{ Str::limit($change['old'], 30) }}
                                                                </span>
                                                            </td>
                                                            <td class="text-success py-1" style="word-break: break-all;">
                                                                @if($change['type'] === 'info')
                                                                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill fw-normal">
                                                                        {{ Str::limit($change['new'], 100) }}
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill fw-normal">
                                                                        {{ Str::limit($change['new'], 30) }}
                                                                    </span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @else
                                    <small class="text-muted italic">No attribute changes recorded</small>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $log->ip_address }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No activity logs found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $logs->links() }}
        </div>
    </div>
</x-admin-layout>
