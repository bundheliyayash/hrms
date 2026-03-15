<x-client-portal-layout>
    <div class="d-md-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-geo-alt-fill text-primary me-2"></i>{{ $site->site_name }}
            </h4>
            <p class="text-muted small mb-0">Mark attendance for employees assigned to this site</p>
        </div>
        <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill mt-2 mt-md-0">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <style>
        .attendance-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
        }
        .attendance-card .card-header-custom {
            background: linear-gradient(135deg, #0f4c75 0%, #3282b8 100%);
            color: #fff;
            padding: 1rem 1.25rem;
        }
        .form-control-time {
            width: 120px;
            text-align: center;
        }
        .employee-row {
            border-bottom: 1px solid #f1f5f9;
            padding: 0.75rem 1.25rem;
            transition: background 0.15s;
        }
        .employee-row:hover { background-color: #f8fafc; }
        .employee-row:last-child { border-bottom: none; }
        .emp-avatar {
            width: 36px;
            height: 36px;
            background: #eff6ff;
            color: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
            flex-shrink: 0;
        }
        .existing-badge {
            background: #ecfdf5;
            color: #10b981;
            font-size: 0.7rem;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
            font-weight: 600;
        }
        .date-selector {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
        }
    </style>

    <!-- Date Selector -->
    <div class="date-selector d-flex align-items-center gap-3">
        <i class="bi bi-calendar3 text-primary fs-5"></i>
        <div class="flex-grow-1">
            <label class="form-label small fw-bold mb-1">Select Date</label>
            <form method="GET" action="{{ route('client.attendance.form', $site->id) }}" class="d-flex align-items-center gap-2" id="dateForm">
                <input type="date" name="date" value="{{ $date }}" max="{{ now()->format('Y-m-d') }}" 
                       class="form-control form-control-sm" style="max-width: 200px;" onchange="this.form.submit()">
            </form>
        </div>
        <div class="text-end">
            <span class="text-muted small">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</span>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Please fix the errors below:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Existing Records -->
    @if($existingRecords->isNotEmpty())
        <div class="attendance-card mb-4">
            <div class="card-header-custom" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-check-circle me-2"></i>Already Recorded ({{ $existingRecords->count() }})
                </h6>
            </div>
            <div>
                @foreach($existingRecords as $record)
                    <div class="employee-row d-flex align-items-center">
                        <div class="emp-avatar me-3" style="background: #ecfdf5; color: #10b981;">
                            {{ strtoupper(substr($record->user->name ?? '?', 0, 2)) }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold small">{{ $record->user->name ?? 'Unknown' }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">
                                {{ $record->user->employeeDetail->employee_id ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="text-center me-4">
                            <div class="text-muted" style="font-size: 0.65rem;">CLOCK IN</div>
                            <div class="fw-bold small text-success">{{ $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('h:i A') : '-' }}</div>
                        </div>
                        <div class="text-center me-3">
                            <div class="text-muted" style="font-size: 0.65rem;">CLOCK OUT</div>
                            <div class="fw-bold small text-danger">{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('h:i A') : '-' }}</div>
                        </div>
                        <span class="existing-badge">
                            <i class="bi bi-check me-1"></i>Recorded
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- New Attendance Form -->
    @php
        $pendingEmployees = $employees->filter(fn($emp) => !in_array($emp->user_id, $existingAttendance));
    @endphp

    @if($pendingEmployees->isNotEmpty())
        <form method="POST" action="{{ route('client.attendance.store') }}" id="attendanceForm">
            @csrf
            <input type="hidden" name="site_id" value="{{ $site->id }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="attendance-card">
                <div class="card-header-custom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Mark Attendance ({{ $pendingEmployees->count() }} employees)
                    </h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll" style="cursor:pointer;">
                        <label class="form-check-label small" for="selectAll" style="cursor:pointer;">Select All</label>
                    </div>
                </div>

                <div>
                    @foreach($pendingEmployees as $index => $emp)
                        <div class="employee-row d-flex align-items-center flex-wrap" id="empRow{{ $emp->user_id }}">
                            <div class="form-check me-3">
                                <input class="form-check-input emp-checkbox" type="checkbox" 
                                       id="check{{ $emp->user_id }}" data-userid="{{ $emp->user_id }}"
                                       onchange="toggleEmployee(this, {{ $emp->user_id }})">
                            </div>
                            <div class="emp-avatar me-3">
                                {{ strtoupper(substr($emp->user->name, 0, 2)) }}
                            </div>
                            <div class="flex-grow-1 min-w-0 me-3">
                                <div class="fw-bold small text-truncate">{{ $emp->user->name }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    ID: {{ $emp->employee_id }} &bull; {{ $emp->designation ?? 'Staff' }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                                <div>
                                    <label class="form-label mb-0" style="font-size: 0.65rem; color: #94a3b8;">CLOCK IN</label>
                                    <input type="time" class="form-control form-control-sm form-control-time time-input" 
                                           id="clockIn{{ $emp->user_id }}" disabled>
                                </div>
                                <div>
                                    <label class="form-label mb-0" style="font-size: 0.65rem; color: #94a3b8;">CLOCK OUT</label>
                                    <input type="time" class="form-control form-control-sm form-control-time time-input" 
                                           id="clockOut{{ $emp->user_id }}" disabled>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Hidden inputs container (populated via JS) -->
            <div id="hiddenInputs"></div>

            <div class="d-flex justify-content-end mt-3 gap-2">
                <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4" id="submitBtn" disabled>
                    <i class="bi bi-check-circle me-1"></i> Submit Attendance
                </button>
            </div>
        </form>
    @else
        <div class="attendance-card text-center py-5">
            <i class="bi bi-check-circle text-success display-4 mb-3 d-block"></i>
            <h6 class="fw-bold">All Done!</h6>
            <p class="text-muted small">All employees have been marked for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}.</p>
        </div>
    @endif

    <script>
        // Select All toggle
        document.getElementById('selectAll')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.emp-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                toggleEmployee(cb, cb.dataset.userid);
            });
        });

        function toggleEmployee(checkbox, userId) {
            const clockIn = document.getElementById('clockIn' + userId);
            const clockOut = document.getElementById('clockOut' + userId);

            if (checkbox.checked) {
                clockIn.disabled = false;
                clockOut.disabled = false;
                clockIn.required = true;
                // Set default times if empty
                if (!clockIn.value) clockIn.value = '09:00';
                if (!clockOut.value) clockOut.value = '18:00';
            } else {
                clockIn.disabled = true;
                clockOut.disabled = true;
                clockIn.required = false;
                clockIn.value = '';
                clockOut.value = '';
            }

            updateSubmitButton();
            updateHiddenInputs();
        }

        function updateSubmitButton() {
            const anyChecked = document.querySelectorAll('.emp-checkbox:checked').length > 0;
            document.getElementById('submitBtn').disabled = !anyChecked;
        }

        function updateHiddenInputs() {
            const container = document.getElementById('hiddenInputs');
            container.innerHTML = '';

            const checked = document.querySelectorAll('.emp-checkbox:checked');
            checked.forEach((cb, index) => {
                const userId = cb.dataset.userid;
                const clockIn = document.getElementById('clockIn' + userId).value;
                const clockOut = document.getElementById('clockOut' + userId).value;

                container.innerHTML += `
                    <input type="hidden" name="entries[${index}][user_id]" value="${userId}">
                    <input type="hidden" name="entries[${index}][clock_in]" value="${clockIn}">
                    <input type="hidden" name="entries[${index}][clock_out]" value="${clockOut}">
                `;
            });
        }

        // Update hidden inputs on time change
        document.querySelectorAll('.time-input').forEach(input => {
            input.addEventListener('change', updateHiddenInputs);
        });

        // Update hidden inputs before form submit
        document.getElementById('attendanceForm')?.addEventListener('submit', function(e) {
            updateHiddenInputs();
        });
    </script>
</x-client-portal-layout>
