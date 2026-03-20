<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <span>My Attendance</span>
            <div class="d-flex gap-2 align-items-center">
                <!-- Date Range Filter -->
                <form action="{{ route('employee.attendance.index') }}" method="GET" class="d-flex gap-2 align-items-center">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="dateRangeToggle" onchange="toggleDateRange()">
                        <label class="form-check-label small" for="dateRangeToggle">Date Range</label>
                    </div>
                    
                    <div id="monthYearFilter" class="d-flex gap-2">
                        <select name="month" class="form-select form-select-sm" style="width: 120px;" onchange="this.form.submit()">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                        <select name="year" class="form-select form-select-sm" style="width: 100px;" onchange="this.form.submit()">
                            @foreach(range(date('Y')-1, date('Y')+1) as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div id="dateRangeFilter" class="d-none d-flex gap-2">
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}" placeholder="Start Date">
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}" placeholder="End Date">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    </div>
                </form>
                
                <div class="d-flex gap-1 me-2">
                    <a href="{{ route('employee.attendance.index', ['month' => \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth()->format('m'), 'year' => \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth()->format('Y')]) }}" 
                       class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
                        <i class="bi bi-chevron-left me-1"></i> Prev
                    </a>
                    <a href="{{ route('employee.attendance.index', ['month' => \Carbon\Carbon::createFromDate($year, $month, 1)->addMonth()->format('m'), 'year' => \Carbon\Carbon::createFromDate($year, $month, 1)->addMonth()->format('Y')]) }}" 
                       class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
                        Next <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </div>
                
                <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#correctionModal">
                    <i class="bi bi-plus-circle me-1"></i> Request Correction
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        .summary-bar {
            background: #fff;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
            padding: 15px 0;
            overflow-x: auto;
            white-space: nowrap;
        }
        .summary-item {
            display: inline-block;
            padding: 0 25px;
            border-right: 1px solid #eee;
            text-align: center;
        }
        .summary-item:last-child { border-right: none; }
        .summary-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 2px;
        }
        .summary-label {
            font-size: 0.75rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .attendance-table th {
            background: #f8f9fa;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #666;
            font-weight: 600;
            border-top: none;
            padding: 12px 15px;
        }
        .attendance-table td {
            font-size: 0.85rem;
            padding: 12px 15px;
            vertical-align: middle;
        }
        .day-status {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .status-fd { background: #e8f5e9; color: #2e7d32; } /* Full Day */
        .status-wk { background: #e3f2fd; color: #1565c0; } /* Weekend */
        .status-p { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; padding: 2px 10px; border-radius: 20px; }
        .status-l { background: #fff3e0; color: #ef6c00; border: 1px solid #ffe0b2; padding: 2px 10px; border-radius: 20px; }
    </style>

    <!-- Summary Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="summary-bar">
            <div class="summary-item">
                <div class="summary-value text-primary">{{ floor(($summary['working_hours'] ?? 0) / 60) }}H : {{ ($summary['working_hours'] ?? 0) % 60 }}M</div>
                <div class="summary-label">Working Hours</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $summary['clock_in_days'] ?? 0 }}</div>
                <div class="summary-label">Clock In Days</div>
            </div>
            <div class="summary-item">
                <div class="summary-value text-danger">{{ $summary['late_in'] ?? 0 }}</div>
                <div class="summary-label">Late IN</div>
            </div>
            <div class="summary-item">
                <div class="summary-value text-warning">{{ $summary['early_out'] ?? 0 }}</div>
                <div class="summary-label">Early OUT</div>
            </div>
            <div class="summary-item">
                <div class="summary-value text-info">{{ $summary['on_leave'] ?? 0 }}</div>
                <div class="summary-label">On Leave</div>
            </div>
            <div class="summary-item">
                <div class="summary-value text-success">Present</div>
                <div class="summary-label">Paid Leave</div>
            </div>
        </div>
    </div>

    <!-- Clock In/Out Live Control (Livewire) -->
    <div class="row mb-4">
        <div class="col-md-5 col-lg-4">
            @livewire('employee-clock-panel')
        </div>
    </div>

    <!-- Attendance Timesheet Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">Timesheet ({{ date('F Y', mktime(0, 0, 0, $month, 1)) }})</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover attendance-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status/Day</th>
                            <th>Attendance</th>
                            <th>Clock IN / OUT</th>
                            <th>Duration</th>
                            <th>Break</th>
                            <th>Productive</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $record)
                        <tr class="{{ !$record->exists ? 'table-light opacity-75' : '' }}">
                            <td class="small fw-bold text-dark">
                                {{ \Carbon\Carbon::parse($record->date)->format('d M, Y') }}
                                <div class="text-muted fs-xs">{{ \Carbon\Carbon::parse($record->date)->format('l') }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border border-primary-subtle rounded-pill">FULL DAY</span>
                            </td>
                            <td>
                                @if($record->exists)
                                    <span class="badge {{ $record->status == 'late' ? 'bg-warning text-dark' : 'bg-success' }} rounded-pill px-3">
                                        {{ strtoupper($record->status) }}
                                    </span>
                                @else
                                    <span class="badge bg-danger rounded-pill px-3">ABSENT</span>
                                @endif
                            </td>
                            <td class="small">
                                @if($record->exists && $record->clock_in)
                                    <div class="fw-bold text-primary">{{ \Carbon\Carbon::parse($record->clock_in)->format('H:i') }} - {{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('H:i') : '...' }}</div>
                                @else
                                    <span class="text-muted">--:--</span>
                                @endif
                            </td>
                            <td class="small">
                                @if($record->exists && $record->clock_out)
                                    @php $totalMin = $record->duration_minutes + $record->breaks()->sum('duration_minutes'); @endphp
                                    {{ floor($totalMin / 60) }}h {{ $totalMin % 60 }}m
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-muted fs-xs">
                                @if($record->exists)
                                    {{ floor($record->breaks()->sum('duration_minutes') / 60) }}h {{ $record->breaks()->sum('duration_minutes') % 60 }}m
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-primary fw-bold small">
                                @if($record->exists && $record->clock_out)
                                    {{ floor($record->duration_minutes / 60) }}h {{ $record->duration_minutes % 60 }}m
                                @else
                                    -
                                @endif
                            </td>
                            <td class="small text-truncate" style="max-width: 100px;">{{ $record->exists && $record->site ? $record->site->site_name : '-' }}</td>
                            <td>
                                @if($record->exists)
                                    @php 
                                        $dayCorrection = $corrections->get($record->date)?->first();
                                    @endphp

                                    @if($dayCorrection)
                                        @if($dayCorrection->status === 'pending')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2" style="font-size: 0.65rem;">
                                                <i class="bi bi-clock-history me-1"></i> Pending
                                            </span>
                                        @elseif($dayCorrection->status === 'approved')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2" style="font-size: 0.65rem;">
                                                <i class="bi bi-check-circle me-1"></i> Fixed
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2" style="font-size: 0.65rem;">
                                                <i class="bi bi-x-circle me-1"></i> Denied
                                            </span>
                                        @endif
                                    @else
                                        <div class="dropdown">
                                            <button class="btn btn-link btn-sm p-0 text-muted" data-bs-toggle="dropdown" aria-expanded="false" title="Request Correction">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                <li>
                                                    <a class="dropdown-item small" href="#" data-bs-toggle="modal" data-bs-target="#correctionModal" 
                                                       onclick="prefillCorrectionModal('{{ $record->date }}', '{{ $record->clock_in }}', '{{ $record->clock_out }}')">
                                                        <i class="bi bi-pencil me-2"></i> Request Correction
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">No records found for this period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white border-0">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>

    <!-- Correction Modal -->
    <div class="modal fade" id="correctionModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('employee.attendance.storeCorrection') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="requested_type" value="time_correction">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Request Time Correction</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info small mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Update the clock-in/out times below. Admin will review and approve your request.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Date</label>
                            <input type="date" name="requested_date" id="modal_date" class="form-control" readonly required>
                        </div>
                        
                        <div class="row gx-2">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Current Clock In</label>
                                <input type="time" id="modal_current_clock_in" class="form-control" readonly style="background-color: #f8f9fa;">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Current Clock Out</label>
                                <input type="time" id="modal_current_clock_out" class="form-control" readonly style="background-color: #f8f9fa;">
                            </div>
                        </div>
                        
                        <div class="row gx-2">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold text-primary">New Clock In</label>
                                <input type="time" name="requested_clock_in" id="modal_new_clock_in" class="form-control" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold text-primary">New Clock Out</label>
                                <input type="time" name="requested_clock_out" id="modal_new_clock_out" class="form-control">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Reason (Required)</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Explain why you need this correction..." required></textarea>
                        </div>
                        <div class="mb-1">
                            <label class="form-label small fw-bold">Attachment (Optional)</label>
                            <input type="file" name="attachment" class="form-control form-control-sm">
                            <small class="text-muted">Upload proof if available</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Submit Request</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const btn = document.getElementById('get-location-btn');
        const spinner = document.getElementById('location-spinner');
        const form = document.getElementById('clock-in-form');
        const latInput = document.getElementById('latitude');
        const longInput = document.getElementById('longitude');

        if(btn) {
            btn.addEventListener('click', () => {
                btn.classList.add('d-none');
                if(spinner) spinner.classList.remove('d-none');

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition((pos) => {
                        if(spinner) spinner.classList.add('d-none');
                        if(form) form.classList.remove('d-none');
                        latInput.value = pos.coords.latitude;
                        longInput.value = pos.coords.longitude;
                    }, () => {
                        alert("Location access failed.");
                        btn.classList.remove('d-none');
                        if(spinner) spinner.classList.add('d-none');
                    }, { enableHighAccuracy: true });
                }
            });
        }

        // Pre-fill correction modal with selected date's data
        function prefillCorrectionModal(date, clockIn, clockOut) {
            const modal = document.getElementById('correctionModal');
            
            // Set the date
            const dateInput = modal.querySelector('#modal_date');
            if (dateInput) {
                dateInput.value = date;
            }
            
            // Set current times (readonly fields)
            const currentClockInInput = modal.querySelector('#modal_current_clock_in');
            const currentClockOutInput = modal.querySelector('#modal_current_clock_out');
            
            if (currentClockInInput) {
                currentClockInInput.value = clockIn || '';
            }
            if (currentClockOutInput) {
                currentClockOutInput.value = clockOut || '';
            }
            
            // Pre-fill new times with current values
            const newClockInInput = modal.querySelector('#modal_new_clock_in');
            const newClockOutInput = modal.querySelector('#modal_new_clock_out');
            
            if (newClockInInput) {
                newClockInInput.value = clockIn || '';
            }
            if (newClockOutInput) {
                newClockOutInput.value = clockOut || '';
            }
        }
        
        function toggleDateRange() {
            const toggle = document.getElementById('dateRangeToggle');
            const monthYearFilter = document.getElementById('monthYearFilter');
            const dateRangeFilter = document.getElementById('dateRangeFilter');
            
            if (toggle.checked) {
                monthYearFilter.classList.add('d-none');
                dateRangeFilter.classList.remove('d-none');
            } else {
                monthYearFilter.classList.remove('d-none');
                dateRangeFilter.classList.add('d-none');
            }
        }
    </script>

</x-admin-layout>
