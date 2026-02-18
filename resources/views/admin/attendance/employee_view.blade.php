<x-admin-layout>
    <!-- Header & Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-4 mb-3 mb-lg-0">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div>
                            <h4 class="mb-0 fw-bold text-dark">{{ $targetUser->name }}</h4>
                            <p class="text-muted small mb-0">{{ $targetUser->employeeDetail->employee_id ?? 'N/A' }} | {{ $targetUser->employeeDetail->designation ?? 'Staff' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap justify-content-lg-end gap-2 align-items-center">
                        <!-- Custom Range -->
                        <form action="{{ route('admin.attendance.index') }}" method="GET" class="d-flex gap-1 align-items-center">
                            <input type="hidden" name="user_id" value="{{ $targetUser->id }}">
                            <div class="input-group input-group-sm" style="width: 320px;">
                                <span class="input-group-text bg-white"><i class="bi bi-calendar-range small"></i></span>
                                <input type="date" name="start_date" class="form-control border-start-0" value="{{ request('start_date', $startDate) }}">
                                <span class="input-group-text bg-white border-start-0 border-end-0">to</span>
                                <input type="date" name="end_date" class="form-control border-start-0" value="{{ request('end_date', $endDate) }}">
                                <button type="submit" class="btn btn-primary px-3">Filter</button>
                            </div>
                        </form>
                        
                        <div class="vr d-none d-md-block mx-2"></div>
                        
                        <!-- Month Quick Select -->
                        <form action="{{ route('admin.attendance.index') }}" method="GET" class="d-flex gap-1">
                            <input type="hidden" name="user_id" value="{{ $targetUser->id }}">
                            <div class="input-group input-group-sm">
                                <select name="month" class="form-select" onchange="this.form.submit()" style="width: 120px;">
                                    @foreach(range(1, 12) as $m)
                                        @php $mVal = sprintf('%02d', $m); @endphp
                                        <option value="{{ $mVal }}" {{ ($month ?? '') == $mVal ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="year" class="form-select" onchange="this.form.submit()" style="width: 90px;">
                                    @foreach(range(date('Y')-2, date('Y')+1) as $y)
                                        <option value="{{ $y }}" {{ ($year ?? '') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>

                        <a href="{{ route('admin.attendance.index', ['user_id' => $targetUser->id, 'month' => \Carbon\Carbon::now()->subMonth()->format('m'), 'year' => \Carbon\Carbon::now()->subMonth()->format('Y')]) }}" 
                           class="btn btn-sm btn-outline-info">
                            Last Month
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white overflow-hidden" style="min-height: 110px;">
                <div class="card-body position-relative d-flex flex-column justify-content-center">
                    <div class="position-absolute end-0 top-0 mt-2 me-3 opacity-25">
                        <i class="bi bi-clock-history display-5"></i>
                    </div>
                    <div class="small fw-bold text-uppercase mb-1 opacity-75">Working Hours</div>
                    @php 
                        $hours = floor($summary['working_hours'] / 60);
                        $mins = $summary['working_hours'] % 60;
                    @endphp
                    <div class="h3 mb-0 fw-bold">{{ $hours }}h {{ $mins }}m</div>
                    <div class="small mt-1 opacity-75 text-truncate">Total Net Duration</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-white" style="min-height: 110px;">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="small fw-bold text-uppercase text-muted">Clock in Days</div>
                        <div class="bg-success-subtle text-success rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-person-check-fill small"></i>
                        </div>
                    </div>
                    <div class="h3 mb-0 fw-bold text-dark">{{ $summary['clock_in_days'] }}</div>
                    <div class="small text-success mt-1 fw-medium">Active Logs</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-white" style="min-height: 110px;">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="small fw-bold text-uppercase text-muted">Status Alerts</div>
                        <div class="bg-warning-subtle text-warning rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-exclamation-triangle-fill small"></i>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ $summary['late_in'] }}</div>
                            <div class="small text-muted fw-medium fs-xs uppercase">Late</div>
                        </div>
                        <div class="vr bg-light"></div>
                        <div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ $summary['early_out'] }}</div>
                            <div class="small text-muted fw-medium fs-xs uppercase">Early</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-white" style="min-height: 110px;">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="small fw-bold text-uppercase text-muted">Off-Days</div>
                        <div class="bg-info-subtle text-info rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-calendar-event-fill small"></i>
                        </div>
                    </div>
                    <div class="h3 mb-0 fw-bold text-dark">{{ $summary['holiday'] ?? 0 }}</div>
                    <div class="small text-muted mt-1 fw-medium">Govt Holidays</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timesheet Table -->
    <div class="card border-0 shadow-sm overflow-hidden mb-5">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-task me-2"></i>Daily Attendance Records</h6>
            <div class="badge bg-light text-primary border-primary-subtle border px-3 py-2 rounded-pill small fw-medium">
                 {{ \Carbon\Carbon::parse($startDate)->format('d M') }} – {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="px-4 py-3">Date & Day</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Clock In</th>
                            <th class="py-3">Clock Out</th>
                            <th class="py-3">Net Work Time</th>
                            <th class="py-3">Break Time</th>
                            <th class="py-3">Productive Time</th>
                            <th class="py-3">Location / Site</th>
                            <th class="py-3 text-end px-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $record)
                        @php 
                            $isVirtual = !$record->id;
                            $breakMins = $record->breaks ? $record->breaks->sum('duration_minutes') : 0; 
                        @endphp
                        <tr class="{{ $isVirtual ? 'bg-light-subtle opacity-75' : '' }}">
                            <td class="px-4">
                                <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($record->date)->format('d M, Y') }}</div>
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($record->date)->format('l') }}</div>
                            </td>
                            <td>
                                @php
                                    $badgeClass = 'bg-danger';
                                    if ($record->status == 'present') $badgeClass = 'bg-success';
                                    elseif ($record->status == 'late') $badgeClass = 'bg-warning text-dark';
                                    elseif ($record->status == 'holiday') $badgeClass = 'bg-info text-dark';
                                @endphp
                                <span class="badge {{ $badgeClass }} border-0 px-3 rounded-pill small">
                                    {{ strtoupper($record->status) }}
                                </span>
                            </td>
                            <td class="fw-bold text-dark">{{ $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('h:i A') : '--:--' }}</td>
                            <td class="fw-bold text-dark">{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('h:i A') : '--:--' }}</td>
                            <td>
                                @if($record->duration_minutes)
                                    {{ floor($record->duration_minutes / 60) }}h {{ $record->duration_minutes % 60 }}m
                                @else
                                    <span class="text-muted small">--</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $breakMins > 0 ? floor($breakMins/60).'h '.($breakMins%60).'m' : '0h 0m' }}</td>
                            <td class="fw-bold text-primary">
                                @php $productive = max(0, $record->duration_minutes - $breakMins); @endphp
                                {{ $productive > 0 ? floor($productive/60).'h '.($productive%60).'m' : '--' }}
                            </td>
                            <td>
                                @if($record->site)
                                    <div class="small text-muted text-truncate" style="max-width: 140px;" title="{{ $record->site->site_name ?? 'N/A' }}">
                                        <i class="bi bi-geo-alt me-1 text-primary"></i> {{ $record->site->site_name ?? 'Field Work' }}
                                    </div>
                                @else
                                    <span class="text-muted small">---</span>
                                @endif
                            </td>
                            <td class="text-end px-4">
                                @if(!$isVirtual)
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle shadow-none" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                                        <li><a class="dropdown-item py-2 small" href="#" data-bs-toggle="modal" data-bs-target="#editModal{{ $record->id }}"><i class="bi bi-pencil-square me-2 text-primary"></i> Manual Override</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.attendance.lock', $record->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item py-2 small text-danger"><i class="bi bi-lock-fill me-2"></i> Lock Record</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                                @else
                                    <span class="text-muted small px-2">-</span>
                                @endif
                            </td>
                        </tr>

                        @if(!$isVirtual)
                        <!-- Override Modal -->
                        <div class="modal fade" id="editModal{{ $record->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('admin.attendance.update', $record->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">Manual Correction</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <p class="text-muted small mb-4">You are modifying attendance for <strong>{{ \Carbon\Carbon::parse($record->date)->format('d M, Y') }}</strong>.</p>
                                            <div class="row g-3">
                                                <div class="col-6">
                                                    <label class="form-label small fw-bold text-muted">Clock In</label>
                                                    <input type="time" name="clock_in" class="form-control" value="{{ $record->clock_in }}">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-bold text-muted">Clock Out</label>
                                                    <input type="time" name="clock_out" class="form-control" value="{{ $record->clock_out }}">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted">Duty Status</label>
                                                    <select name="status" class="form-select">
                                                        @foreach(['present', 'absent', 'late', 'early_out', 'half_day', 'missing', 'holiday'] as $status)
                                                            <option value="{{ $status }}" {{ $record->status == $status ? 'selected' : '' }}>{{ strtoupper($status) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted">Audit Remark / Reason</label>
                                                    <textarea name="reason" class="form-control" rows="2" placeholder="Explain why this change is being made..." required></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 p-4 pt-0">
                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4">Apply Changes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr><td colspan="9" class="text-center py-5 text-muted">No attendance data found for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .table thead th { font-weight: 600; letter-spacing: 0.5px; border-bottom-width: 0; font-size: 0.7rem; }
        .table tbody td { border-bottom-color: #f8fafc; font-size: 0.85rem; }
        .bg-success-subtle { background-color: #f0fdf4 !important; }
        .bg-warning-subtle { background-color: #fffbeb !important; }
        .bg-info-subtle { background-color: #f0f9ff !important; }
        .fs-xs { font-size: 0.65rem; }
        .card { transition: all 0.2s ease-in-out; }
    </style>
</x-admin-layout>
