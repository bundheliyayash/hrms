<x-admin-layout>
    <x-slot name="header">
        Attendance Report
    </x-slot>

    <div class="mb-3">
        <a href="{{ route('admin.reports.index') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted">Daily Log</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Date</th>
                            <th>Employee</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Break (Mins)</th>
                            <th>Site Check</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $record)
                        <tr>
                            <td class="px-3">{{ $record->date }}</td>
                            <td class="fw-bold">{{ $record->user->name }}</td>
                            <td>{{ $record->clock_in }}</td>
                            <td>{{ $record->clock_out ?? '-' }}</td>
                            <td>
                                @if($record->total_break_minutes > 0)
                                    <span class="badge bg-light text-dark border">{{ $record->total_break_minutes }} min</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($record->is_verified)
                                    <span class="badge bg-info text-dark" title="{{ $record->distance_detected }}m away">
                                        <i class="bi bi-geo-alt-fill"></i> Verified
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Manual</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $record->status == 'present' ? 'bg-success' : ($record->status == 'absent' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $attendances->links() }}
        </div>
    </div>
</x-admin-layout>
