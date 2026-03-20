<x-client-portal-layout>
    <style>
        .hist-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; }
        .hist-header { background:linear-gradient(135deg,#0f4c75 0%,#3282b8 100%); color:#fff; padding:.9rem 1.25rem; }
        .badge-source { font-size:.65rem; padding:2px 7px; border-radius:20px; }
    </style>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-clock-history me-2 text-primary"></i>Attendance History</h4>
            <p class="text-muted small mb-0">All records submitted through your portal</p>
        </div>
        <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 mb-3"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</div>
    @endif

    <div class="hist-card">
        <div class="hist-header d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i>Records ({{ $records->total() }} total)</h6>
        </div>

        @if($records->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                No attendance records found.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Site</th>
                            <th>Date</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $rec)
                            <tr>
                                <td>
                                    <div class="fw-bold small">{{ $rec->user->name ?? '—' }}</div>
                                    <div class="text-muted" style="font-size:.7rem;">{{ $rec->user->employeeDetail->employee_id ?? '' }}</div>
                                </td>
                                <td class="small">{{ $rec->site->site_name ?? '—' }}</td>
                                <td class="small fw-bold">{{ \Carbon\Carbon::parse($rec->date)->format('d M Y') }}</td>
                                <td class="small text-success">{{ $rec->clock_in ? substr($rec->clock_in, 0, 5) : '—' }}</td>
                                <td class="small text-danger">{{ $rec->clock_out ? substr($rec->clock_out, 0, 5) : '—' }}</td>
                                <td class="small">
                                    @if($rec->duration_minutes)
                                        {{ floor($rec->duration_minutes / 60) }}h {{ $rec->duration_minutes % 60 }}m
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($rec->is_locked)
                                        <span class="badge bg-secondary-subtle text-secondary border badge-source">Locked</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border badge-source">Active</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($records->hasPages())
                <div class="p-3">{{ $records->links() }}</div>
            @endif
        @endif
    </div>
</x-client-portal-layout>
