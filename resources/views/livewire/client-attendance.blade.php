<div>
    <style>
        .att-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; }
        .att-header { background:linear-gradient(135deg,#0f4c75 0%,#3282b8 100%); color:#fff; padding:.9rem 1.25rem; }
        .att-header-green { background:linear-gradient(135deg,#10b981 0%,#059669 100%); }
        .emp-row { border-bottom:1px solid #f1f5f9; padding:.65rem 1.25rem; transition:background .15s; }
        .emp-row:hover { background:#f8fafc; }
        .emp-row:last-child { border-bottom:none; }
        .emp-avatar { width:36px;height:36px;background:#eff6ff;color:#3b82f6;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.8rem;flex-shrink:0; }
        .time-input { width:110px; }
    </style>

    {{-- Excel Import/Export toolbar --}}
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <div class="me-auto">
            <span class="text-muted small"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Bulk upload via Excel</span>
        </div>

        {{-- Download Template --}}
        <a href="{{ route('client.attendance.template', ['site' => $site->id, 'date' => $date]) }}"
           class="btn btn-outline-success btn-sm rounded-pill px-3">
            <i class="bi bi-download me-1"></i> Download Template
        </a>

        {{-- Upload Form (collapsible) --}}
        <div x-data="{ open: false }">
            <button x-on:click="open = !open"
                    class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="bi bi-upload me-1"></i> Upload Filled Sheet
            </button>

            <div x-show="open" x-transition class="mt-2 p-3 border rounded-3 bg-white shadow-sm" style="min-width:320px;position:absolute;z-index:100;right:0;">
                <form method="POST" action="{{ route('client.attendance.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="site_id" value="{{ $site->id }}">
                    <input type="hidden" name="date" value="{{ $date }}">

                    <p class="small text-muted mb-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Download the template → fill Clock In &amp; Clock Out in HH:MM → upload here.
                    </p>

                    <div class="mb-2">
                        <label class="form-label small fw-bold mb-1">Select Excel file (.xlsx)</label>
                        <input type="file" name="attendance_file" accept=".xlsx,.xls"
                               class="form-control form-control-sm" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 flex-grow-1">
                            <i class="bi bi-cloud-upload me-1"></i> Import
                        </button>
                        <button type="button" x-on:click="open = false"
                                class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Import errors from session --}}
    @if(session('import_errors'))
        <div class="alert alert-warning py-2 mb-3">
            <strong><i class="bi bi-exclamation-triangle me-1"></i>Some rows had issues:</strong>
            <ul class="mb-0 mt-1">
                @foreach(session('import_errors') as $err)
                    <li class="small">{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Date Picker --}}
    <div class="d-flex align-items-center gap-3 bg-white border border-light-subtle rounded-3 p-3 mb-3">
        <i class="bi bi-calendar3 text-primary fs-5"></i>
        <div>
            <label class="form-label small fw-bold mb-1">Select Date</label>
            <input type="date" wire:model.live="date"
                   max="{{ now()->format('Y-m-d') }}"
                   class="form-control form-control-sm" style="max-width:200px;">
        </div>
        <div class="ms-auto text-muted small">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</div>
    </div>

    {{-- Status Message --}}
    @if($statusMessage)
        <div class="alert alert-{{ $statusType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show py-2 mb-3" role="alert">
            <i class="bi bi-{{ $statusType === 'success' ? 'check-circle' : 'exclamation-triangle' }} me-2"></i>
            {{ $statusMessage }}
            <button type="button" class="btn-close btn-sm" wire:click="$set('statusMessage', '')"></button>
        </div>
    @endif

    {{-- Already Recorded --}}
    @if(count($existingRecords) > 0)
        <div class="att-card mb-4">
            <div class="att-header att-header-green d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-check-circle me-2"></i>Already Recorded ({{ count($existingRecords) }})
                </h6>
            </div>
            @foreach($existingRecords as $rec)
                <div class="emp-row d-flex align-items-center flex-wrap gap-2">
                    <div class="emp-avatar me-2" style="background:#ecfdf5;color:#10b981;">
                        {{ strtoupper(substr($rec['name'], 0, 2)) }}
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-bold small">{{ $rec['name'] }}</div>
                    </div>

                    @if($editingAttendanceId === $rec['id'])
                        {{-- Inline Edit --}}
                        <div class="d-flex align-items-center gap-2">
                            <div>
                                <label class="form-label mb-0" style="font-size:.65rem;color:#94a3b8;">IN</label>
                                <input type="time" wire:model="editClockIn" class="form-control form-control-sm time-input">
                                @error('editClockIn') <div class="text-danger" style="font-size:.7rem;">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="form-label mb-0" style="font-size:.65rem;color:#94a3b8;">OUT</label>
                                <input type="time" wire:model="editClockOut" class="form-control form-control-sm time-input">
                            </div>
                            <div class="d-flex gap-1 mt-3">
                                <button wire:click="saveEdit" class="btn btn-success btn-sm px-2 py-1">
                                    <i class="bi bi-check"></i>
                                </button>
                                <button wire:click="cancelEdit" class="btn btn-outline-secondary btn-sm px-2 py-1">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="text-center me-3">
                            <div class="text-muted" style="font-size:.65rem;">IN</div>
                            <div class="fw-bold small text-success">{{ $rec['clock_in'] ?: '-' }}</div>
                        </div>
                        <div class="text-center me-3">
                            <div class="text-muted" style="font-size:.65rem;">OUT</div>
                            <div class="fw-bold small text-danger">{{ $rec['clock_out'] ?: '-' }}</div>
                        </div>
                        @if(!$rec['is_locked'])
                            <button wire:click="startEdit({{ $rec['id'] }}, '{{ $rec['clock_in'] }}', '{{ $rec['clock_out'] }}')"
                                    class="btn btn-outline-primary btn-sm rounded-pill px-2 py-0"
                                    title="Edit times">
                                <i class="bi bi-pencil" style="font-size:.75rem;"></i>
                            </button>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.65rem;">Locked</span>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Pending Employees --}}
    @if(count($entries) > 0)
        <div class="att-card">
            <div class="att-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>Mark Attendance ({{ count($entries) }} pending)
                </h6>
                <div class="d-flex gap-2">
                    <button wire:click="selectAll" class="btn btn-sm btn-light rounded-pill px-3" style="font-size:.75rem;">Select All</button>
                    <button wire:click="deselectAll" class="btn btn-sm btn-outline-light rounded-pill px-3" style="font-size:.75rem;">Clear</button>
                </div>
            </div>

            @foreach($entries as $index => $entry)
                <div class="emp-row d-flex align-items-center flex-wrap gap-2">
                    <div class="form-check me-2">
                        <input class="form-check-input" type="checkbox"
                               wire:click="toggleSelect({{ $index }})"
                               {{ $entry['selected'] ? 'checked' : '' }}>
                    </div>
                    <div class="emp-avatar me-2">
                        {{ strtoupper(substr($entry['name'], 0, 2)) }}
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-bold small text-truncate">{{ $entry['name'] }}</div>
                        <div class="text-muted" style="font-size:.7rem;">
                            {{ $entry['employee_id'] }} &bull; {{ $entry['designation'] }}
                        </div>
                    </div>
                    @if($entry['selected'])
                        <div class="d-flex align-items-center gap-2">
                            <div>
                                <label class="form-label mb-0" style="font-size:.65rem;color:#94a3b8;">CLOCK IN</label>
                                <input type="time" wire:model="entries.{{ $index }}.clock_in"
                                       class="form-control form-control-sm time-input">
                            </div>
                            <div>
                                <label class="form-label mb-0" style="font-size:.65rem;color:#94a3b8;">CLOCK OUT</label>
                                <input type="time" wire:model="entries.{{ $index }}.clock_out"
                                       class="form-control form-control-sm time-input">
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="d-flex justify-content-end p-3 gap-2 border-top">
                <button wire:click="submitAttendance"
                        wire:loading.attr="disabled"
                        class="btn btn-primary rounded-pill px-4">
                    <span wire:loading wire:target="submitAttendance" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="bi bi-check-circle me-1" wire:loading.remove wire:target="submitAttendance"></i>
                    Submit Attendance
                </button>
            </div>
        </div>
    @elseif(count($existingRecords) > 0)
        <div class="att-card text-center py-4">
            <i class="bi bi-check-circle text-success display-5 mb-2 d-block"></i>
            <h6 class="fw-bold">All Done!</h6>
            <p class="text-muted small mb-0">All employees marked for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}.</p>
        </div>
    @else
        <div class="att-card text-center py-5 text-muted">
            <i class="bi bi-people display-4 mb-3 d-block"></i>
            <p>No employees assigned to this site for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}.</p>
        </div>
    @endif
</div>
