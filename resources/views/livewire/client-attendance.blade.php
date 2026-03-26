<div>
<style>
    /* ── Layout ───────────────────────────────────────────────── */
    .ca-card       { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; }
    .ca-card + .ca-card { margin-top:1rem; }

    /* ── Section headers ──────────────────────────────────────── */
    .ca-header          { padding:.85rem 1.25rem; display:flex; align-items:center; justify-content:space-between; }
    .ca-header-pending  { background:linear-gradient(135deg,#1d4ed8 0%,#3b82f6 100%); color:#fff; }
    .ca-header-done     { background:linear-gradient(135deg,#065f46 0%,#10b981 100%); color:#fff; }

    /* ── Stats bar ────────────────────────────────────────────── */
    .stat-pill { display:flex; align-items:center; gap:.45rem; background:#f8fafc;
                 border:1px solid #e2e8f0; border-radius:999px; padding:.3rem .85rem;
                 font-size:.78rem; font-weight:600; }
    .stat-dot  { width:8px; height:8px; border-radius:50%; flex-shrink:0; }

    /* ── Employee rows ────────────────────────────────────────── */
    .emp-row         { border-bottom:1px solid #f1f5f9; padding:.7rem 1.25rem;
                       transition:background .12s; }
    .emp-row:last-child { border-bottom:none; }
    .emp-row:hover   { background:#f8fafc; }
    .emp-row.selected-row { background:#eff6ff; border-left:3px solid #3b82f6; }

    /* ── Avatar ───────────────────────────────────────────────── */
    .emp-avatar { width:36px; height:36px; border-radius:50%;
                  display:flex; align-items:center; justify-content:center;
                  font-weight:700; font-size:.75rem; flex-shrink:0; }
    .avatar-pending { background:#eff6ff; color:#3b82f6; }
    .avatar-done    { background:#ecfdf5; color:#059669; }

    /* ── Time inputs ──────────────────────────────────────────── */
    .time-group label { font-size:.6rem; color:#94a3b8; font-weight:600;
                        text-transform:uppercase; letter-spacing:.05em; margin-bottom:.2rem; display:block; }
    .time-input { width:105px; font-size:.82rem; }

    /* ── Duration badge ───────────────────────────────────────── */
    .dur-badge { font-size:.7rem; background:#f0fdf4; color:#16a34a;
                 border:1px solid #bbf7d0; border-radius:6px;
                 padding:.15rem .5rem; white-space:nowrap; }

    /* ── Status badge ─────────────────────────────────────────── */
    .status-badge { font-size:.65rem; padding:.2rem .55rem; border-radius:6px; font-weight:600; }
    .s-present  { background:#ecfdf5; color:#059669; }
    .s-late     { background:#fff7ed; color:#d97706; }
    .s-half_day { background:#eff6ff; color:#2563eb; }
    .s-absent   { background:#fef2f2; color:#dc2626; }

    /* ── Footer bar ───────────────────────────────────────────── */
    .ca-footer { padding:.85rem 1.25rem; border-top:1px solid #f1f5f9;
                 display:flex; justify-content:space-between; align-items:center; }

    /* ── Upload panel ─────────────────────────────────────────── */
    .upload-panel { position:absolute; right:0; top:calc(100% + .5rem); z-index:200;
                    background:#fff; border:1px solid #e2e8f0; border-radius:12px;
                    padding:1rem; box-shadow:0 8px 24px rgba(0,0,0,.10); min-width:300px; }
</style>

{{-- ── Top toolbar: Excel import/export ──────────────────────────── --}}
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <span class="text-muted small me-auto">
        <i class="bi bi-file-earmark-spreadsheet me-1 text-success"></i>
        Bulk upload via Excel
    </span>

    <a href="{{ route('client.attendance.template', ['site' => $site->id, 'date' => $date]) }}"
       class="btn btn-outline-success btn-sm rounded-pill px-3">
        <i class="bi bi-download me-1"></i> Download Template
    </a>

    <div x-data="{ open: false }" class="position-relative">
        <button x-on:click="open = !open"
                class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="bi bi-upload me-1"></i> Upload Filled Sheet
        </button>

        <div x-show="open" x-transition x-on:click.outside="open = false" class="upload-panel">
            <p class="small fw-bold mb-2"><i class="bi bi-info-circle text-primary me-1"></i>How to use</p>
            <p class="small text-muted mb-3" style="line-height:1.5;">
                1. Download the template &nbsp;→&nbsp;
                2. Fill <strong>Clock In</strong> &amp; <strong>Clock Out</strong> in <code>HH:MM</code> format &nbsp;→&nbsp;
                3. Upload below.
            </p>
            <form method="POST" action="{{ route('client.attendance.upload') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="site_id" value="{{ $site->id }}">
                <input type="hidden" name="date" value="{{ $date }}">
                <div class="mb-3">
                    <label class="form-label small fw-semibold mb-1">Select Excel file (.xlsx)</label>
                    <input type="file" name="attendance_file" accept=".xlsx,.xls"
                           class="form-control form-control-sm" required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill flex-grow-1">
                        <i class="bi bi-cloud-upload me-1"></i> Import
                    </button>
                    <button type="button" x-on:click="open = false"
                            class="btn btn-outline-secondary btn-sm rounded-pill px-3">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import errors --}}
@if(session('import_errors'))
    <div class="alert alert-warning py-2 mb-3 small">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Some rows had issues:</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ── Date picker + summary stats ────────────────────────────────── --}}
<div class="d-flex align-items-center gap-3 bg-white border rounded-3 p-3 mb-4 flex-wrap">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-calendar3 text-primary fs-5"></i>
        <div>
            <div class="text-muted" style="font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Attendance Date</div>
            <input type="date" wire:model.live="date"
                   max="{{ now()->format('Y-m-d') }}"
                   class="form-control form-control-sm border-0 p-0 fw-bold"
                   style="font-size:1rem;width:150px;">
        </div>
    </div>

    <div class="vr d-none d-md-block"></div>

    {{-- Stats pills --}}
    <div class="d-flex gap-2 flex-wrap ms-md-auto">
        <div class="stat-pill">
            <span class="stat-dot" style="background:#3b82f6;"></span>
            {{ count($entries) }} Pending
        </div>
        <div class="stat-pill">
            <span class="stat-dot" style="background:#10b981;"></span>
            {{ count($existingRecords) }} Marked
        </div>
        @php $total = count($entries) + count($existingRecords); @endphp
        <div class="stat-pill text-muted">
            <i class="bi bi-people" style="font-size:.8rem;"></i>
            {{ $total }} Total
        </div>
        <div class="text-muted small d-flex align-items-center">
            {{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}
        </div>
    </div>
</div>

{{-- Status alert --}}
@if($statusMessage)
    <div class="alert alert-{{ $statusType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show py-2 mb-3 small" role="alert">
        <i class="bi bi-{{ $statusType === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' }} me-2"></i>
        {{ $statusMessage }}
        <button type="button" class="btn-close btn-sm" wire:click="$set('statusMessage', '')"></button>
    </div>
@endif

{{-- ── SECTION 1: Already Recorded ────────────────────────────────── --}}
@if(count($existingRecords) > 0)
    <div class="ca-card mb-3">
        <div class="ca-header ca-header-done">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-6"></i>
                <span class="fw-bold">Recorded Today &nbsp;<span class="badge bg-white text-success ms-1" style="font-size:.7rem;">{{ count($existingRecords) }}</span></span>
            </div>
            <span class="small opacity-75">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
        </div>

        @foreach($existingRecords as $rec)
            <div class="emp-row d-flex align-items-center gap-3 flex-wrap">

                {{-- Avatar --}}
                <div class="emp-avatar avatar-done">{{ strtoupper(substr($rec['name'], 0, 2)) }}</div>

                {{-- Name + ID --}}
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold small text-truncate">{{ $rec['name'] }}</div>
                    <div style="font-size:.68rem;" class="text-muted">
                        {{ $rec['employee_id'] }}
                        @if($rec['designation'])
                            &bull; {{ $rec['designation'] }}
                        @endif
                    </div>
                </div>

                @if($editingAttendanceId === $rec['id'])
                    {{-- Inline edit mode --}}
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                        <div class="time-group">
                            <label>Clock In</label>
                            <input type="time" wire:model="editClockIn" class="form-control form-control-sm time-input">
                            @error('editClockIn') <div class="text-danger" style="font-size:.65rem;">{{ $message }}</div> @enderror
                        </div>
                        <div class="time-group">
                            <label>Clock Out</label>
                            <input type="time" wire:model="editClockOut" class="form-control form-control-sm time-input">
                        </div>
                        <div class="d-flex gap-1">
                            <button wire:click="saveEdit"
                                    class="btn btn-success btn-sm px-3 rounded-pill">
                                <i class="bi bi-check-lg me-1"></i> Save
                            </button>
                            <button wire:click="cancelEdit"
                                    class="btn btn-outline-secondary btn-sm px-3 rounded-pill">Cancel</button>
                        </div>
                    </div>
                @else
                    {{-- Read mode --}}
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        {{-- Status badge --}}
                        @php $s = $rec['status'] ?? 'present'; @endphp
                        <span class="status-badge s-{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</span>

                        {{-- Times --}}
                        <div class="text-center">
                            <div style="font-size:.6rem;color:#94a3b8;font-weight:600;">IN</div>
                            <div class="fw-bold small text-success">{{ $rec['clock_in'] ?: '—' }}</div>
                        </div>
                        <div class="text-center">
                            <div style="font-size:.6rem;color:#94a3b8;font-weight:600;">OUT</div>
                            <div class="fw-bold small text-danger">{{ $rec['clock_out'] ?: '—' }}</div>
                        </div>

                        {{-- Duration --}}
                        @if($rec['duration_minutes'])
                            <span class="dur-badge">
                                <i class="bi bi-clock me-1"></i>
                                {{ floor($rec['duration_minutes'] / 60) }}h {{ $rec['duration_minutes'] % 60 }}m
                            </span>
                        @endif

                        {{-- Edit / Locked --}}
                        @if(!$rec['is_locked'])
                            <button wire:click="startEdit({{ $rec['id'] }}, '{{ $rec['clock_in'] }}', '{{ $rec['clock_out'] }}')"
                                    class="btn btn-light btn-sm rounded-pill px-2 border"
                                    title="Edit times">
                                <i class="bi bi-pencil" style="font-size:.75rem;"></i>
                            </button>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.62rem;">
                                <i class="bi bi-lock me-1"></i> Locked
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif

{{-- ── SECTION 2: Pending Employees ───────────────────────────────── --}}
@if(count($entries) > 0)
    <div class="ca-card">
        <div class="ca-header ca-header-pending">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-pencil-square fs-6"></i>
                <span class="fw-bold">Mark Attendance &nbsp;<span class="badge bg-white text-primary ms-1" style="font-size:.7rem;">{{ count($entries) }} pending</span></span>
            </div>
            <div class="d-flex gap-2">
                <button wire:click="selectAll"
                        class="btn btn-sm btn-light rounded-pill px-3" style="font-size:.73rem;">
                    <i class="bi bi-check-all me-1"></i> Select All
                </button>
                <button wire:click="deselectAll"
                        class="btn btn-sm btn-outline-light rounded-pill px-3" style="font-size:.73rem;">Clear</button>
            </div>
        </div>

        @foreach($entries as $index => $entry)
            <div class="emp-row {{ $entry['selected'] ? 'selected-row' : '' }} d-flex align-items-center gap-3 flex-wrap">

                {{-- Checkbox --}}
                <div class="form-check m-0" style="min-width:20px;">
                    <input class="form-check-input" type="checkbox"
                           wire:click="toggleSelect({{ $index }})"
                           {{ $entry['selected'] ? 'checked' : '' }}
                           style="width:16px;height:16px;cursor:pointer;">
                </div>

                {{-- Avatar --}}
                <div class="emp-avatar avatar-pending">{{ strtoupper(substr($entry['name'], 0, 2)) }}</div>

                {{-- Name + ID --}}
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold small text-truncate">{{ $entry['name'] }}</div>
                    <div style="font-size:.68rem;" class="text-muted">
                        {{ $entry['employee_id'] }} &bull; {{ $entry['designation'] }}
                    </div>
                </div>

                {{-- Time inputs (shown only when selected) --}}
                @if($entry['selected'])
                    <div class="d-flex align-items-end gap-2 flex-wrap">
                        <div class="time-group">
                            <label>Clock In</label>
                            <input type="time" wire:model="entries.{{ $index }}.clock_in"
                                   class="form-control form-control-sm time-input">
                        </div>
                        <div class="time-group">
                            <label>Clock Out</label>
                            <input type="time" wire:model="entries.{{ $index }}.clock_out"
                                   class="form-control form-control-sm time-input">
                        </div>
                    </div>
                @else
                    <span class="text-muted small d-none d-md-block" style="font-size:.72rem;">
                        <i class="bi bi-square text-secondary me-1"></i> Not selected
                    </span>
                @endif
            </div>
        @endforeach

        {{-- Footer with submit --}}
        <div class="ca-footer">
            @php $selectedCount = collect($entries)->where('selected', true)->count(); @endphp
            <span class="text-muted small">
                @if($selectedCount > 0)
                    <i class="bi bi-person-check text-primary me-1"></i>
                    <strong class="text-primary">{{ $selectedCount }}</strong> of {{ count($entries) }} selected
                @else
                    <i class="bi bi-info-circle me-1"></i> Select employees above to mark attendance
                @endif
            </span>
            <button wire:click="submitAttendance"
                    wire:loading.attr="disabled"
                    @if($selectedCount === 0) disabled @endif
                    class="btn btn-primary rounded-pill px-4">
                <span wire:loading wire:target="submitAttendance"
                      class="spinner-border spinner-border-sm me-1"></span>
                <i class="bi bi-check-circle-fill me-1"
                   wire:loading.remove wire:target="submitAttendance"></i>
                Submit Attendance
            </button>
        </div>
    </div>

{{-- All done --}}
@elseif(count($existingRecords) > 0)
    <div class="ca-card text-center py-5">
        <div class="mb-3" style="font-size:2.5rem;">✅</div>
        <h6 class="fw-bold mb-1">All Done for Today!</h6>
        <p class="text-muted small mb-0">
            All {{ count($existingRecords) }} employees marked for
            {{ \Carbon\Carbon::parse($date)->format('d M Y') }}.
        </p>
    </div>

{{-- No employees --}}
@else
    <div class="ca-card text-center py-5 text-muted">
        <i class="bi bi-people display-4 mb-3 d-block text-secondary"></i>
        <h6 class="fw-bold text-dark mb-1">No Employees Assigned</h6>
        <p class="small mb-0">
            No staff assigned to <strong>{{ $site->site_name }}</strong>
            for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}.
        </p>
        <p class="small text-muted mt-1">Please contact your account manager.</p>
    </div>
@endif

</div>
