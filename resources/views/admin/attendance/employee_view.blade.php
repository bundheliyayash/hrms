<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <span>Attendance: {{ $targetUser->name }}</span>
            <div class="d-flex gap-2 align-items-center">
                <form action="{{ route('admin.attendance.index') }}" method="GET" class="d-flex gap-2">
                    <input type="hidden" name="user_id" value="{{ $targetUser->id }}">
                    <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endforeach
                    </select>
                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach(range(date('Y')-1, date('Y')+1) as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                    <i class="bi bi-arrow-left"></i> Back to Daily List
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .summary-bar { background: #fff; border-bottom: 1px solid #eee; margin-bottom: 20px; padding: 15px 0; overflow-x: auto; white-space: nowrap; }
        .summary-item { display: inline-block; padding: 0 25px; border-right: 1px solid #eee; text-align: center; }
        .summary-item:last-child { border-right: none; }
        .summary-value { font-size: 1.1rem; font-weight: 700; color: #333; margin-bottom: 2px; }
        .summary-label { font-size: 0.75rem; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
        .attendance-table th { background: #f8f9fa; font-size: 0.75rem; text-transform: uppercase; color: #666; font-weight: 600; padding: 12px 15px; }
        .attendance-table td { font-size: 0.85rem; padding: 12px 15px; vertical-align: middle; }
        .status-p { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; padding: 2px 10px; border-radius: 20px; }
    </style>

    <!-- Summary Bar -->
    <div class="card border-0 shadow-sm mb-4 text-center">
        <div class="card-header bg-white border-0 py-3">
             <div class="d-flex align-items-center justify-content-center">
                <div class="bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.5rem;">
                    {{ substr($targetUser->name, 0, 1) }}
                </div>
                <div class="text-start">
                    <h5 class="mb-0 fw-bold">{{ $targetUser->name }}</h5>
                    <p class="mb-0 text-muted small">{{ $targetUser->employeeDetail->employee_id ?? 'N/A' }} | {{ $targetUser->employeeDetail->designation ?? 'Employee' }}</p>
                </div>
             </div>
        </div>
        <div class="summary-bar border-top">
            <div class="summary-item">
                <div class="summary-value text-primary">{{ floor($summary['working_hours'] / 60) }}H : {{ $summary['working_hours'] % 60 }}M</div>
                <div class="summary-label">Working Hours</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $summary['clock_in_days'] }}</div>
                <div class="summary-label">Clock In Days</div>
            </div>
            <div class="summary-item">
                <div class="summary-value text-danger">{{ $summary['late_in'] }}</div>
                <div class="summary-label">Late IN</div>
            </div>
            <div class="summary-item">
                <div class="summary-value text-warning">{{ $summary['early_out'] }}</div>
                <div class="summary-label">Early OUT</div>
            </div>
            <div class="summary-item">
                <div class="summary-value text-info">{{ $summary['holiday'] ?? 0 }}</div>
                <div class="summary-label">Holidays</div>
            </div>
        </div>
    </div>

    <!-- Attendance Timesheet Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover attendance-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day Status</th>
                            <th>Status</th>
                            <th>Clock IN</th>
                            <th>Clock OUT</th>
                            <th>Total Time</th>
                            <th>Break Hours</th>
                            <th>Productive Hours</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $record)
                        <tr>
                            <td class="fw-bold text-secondary">{{ \Carbon\Carbon::parse($record->date)->format('D d M, Y') }}</td>
                            <td><span class="badge bg-light text-dark">FD</span></td>
                            <td><span class="status-p">{{ strtoupper($record->status) }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($record->clock_in)->format('h:i A') }}</td>
                            <td>{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('h:i A') : '-' }}</td>
                            <td class="fw-bold">
                                @if($record->clock_out)
                                    {{ floor(($record->duration_minutes + $record->breaks()->sum('duration_minutes')) / 60) }}H : {{ ($record->duration_minutes + $record->breaks()->sum('duration_minutes')) % 60 }}M
                                @else - @endif
                            </td>
                            <td>
                                @php $breakMin = $record->breaks()->sum('duration_minutes'); @endphp
                                {{ floor($breakMin / 60) }}H : {{ $breakMin % 60 }}M
                            </td>
                            <td class="text-primary fw-bold">
                                {{ floor($record->duration_minutes / 60) }}H : {{ $record->duration_minutes % 60 }}M
                            </td>
                            <td>{{ $record->site->site_name ?? 'N/A' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-link btn-sm p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li><a class="dropdown-menu-item px-3 py-2 small" href="#" data-bs-toggle="modal" data-bs-target="#editModal{{ $record->id }}"><i class="bi bi-pencil me-2"></i> Manual Override</a></li>
                                        <li>
                                            <form action="{{ route('admin.attendance.lock', $record->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item px-3 py-2 small text-danger"><i class="bi bi-lock me-2"></i> Lock Record</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- Override Modal (Simplified for Reuse) -->
                        <div class="modal fade" id="editModal{{ $record->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('admin.attendance.update', $record->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Manual Override</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Clock In</label>
                                                <input type="time" name="clock_in" class="form-control" value="{{ $record->clock_in }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Clock Out</label>
                                                <input type="time" name="clock_out" class="form-control" value="{{ $record->clock_out }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Status</label>
                                                <select name="status" class="form-select">
                                                    @foreach(['present', 'absent', 'late', 'early_out', 'half_day', 'missing', 'holiday'] as $status)
                                                        <option value="{{ $status }}" {{ $record->status == $status ? 'selected' : '' }}>{{ strtoupper($status) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Reason (Compulsory Audit)</label>
                                                <textarea name="reason" class="form-control" rows="2" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4">Update History</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        @empty
                        <tr><td colspan="10" class="text-center py-5 text-muted">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
