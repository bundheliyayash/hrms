<div>
    <style>
        .clock-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:1.5rem; }
        .clock-status-badge { font-size:.7rem; padding:.25rem .75rem; border-radius:20px; font-weight:600; }
        .live-clock { font-size:2.5rem; font-weight:700; letter-spacing:.05em; color:#1e293b; font-variant-numeric:tabular-nums; }
    </style>

    {{-- Status Message --}}
    @if($message)
        <div class="alert alert-{{ $msgType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show py-2 mb-3">
            <i class="bi bi-{{ $msgType === 'success' ? 'check-circle' : 'exclamation-triangle' }} me-2"></i>
            {{ $message }}
            <button type="button" class="btn-close btn-sm" wire:click="$set('message', '')"></button>
        </div>
    @endif

    <div class="clock-card">
        {{-- Live Clock --}}
        <div class="text-center mb-3">
            <div class="live-clock" id="liveClock">--:--:--</div>
            <div class="text-muted small">{{ now()->format('l, d M Y') }}</div>
        </div>

        @if(!$todayAttendance)
            {{-- Not clocked in --}}
            <div class="text-center mb-3">
                <span class="clock-status-badge bg-secondary-subtle text-secondary">Not Clocked In</span>
            </div>
            <div class="text-center"
                 x-data="{ locating: false, error: '' }"
                 x-on:click.outside="error = ''">

                <div x-show="locating" class="text-muted small mb-2">
                    <div class="spinner-border spinner-border-sm me-1"></div> Getting location...
                </div>
                <div x-show="error" class="text-danger small mb-2" x-text="error"></div>

                <button
                    x-on:click="
                        locating = true; error = '';
                        navigator.geolocation.getCurrentPosition(
                            pos => {
                                $wire.latitude  = pos.coords.latitude;
                                $wire.longitude = pos.coords.longitude;
                                locating = false;
                                $wire.clockIn();
                            },
                            err => {
                                locating = false;
                                // Clock in without GPS (site may not require it)
                                $wire.clockIn();
                            },
                            { timeout: 8000 }
                        );
                    "
                    wire:loading.attr="disabled"
                    class="btn btn-success btn-lg rounded-pill px-5">
                    <span wire:loading wire:target="clockIn" class="spinner-border spinner-border-sm me-2"></span>
                    <i class="bi bi-geo-alt-fill me-2" wire:loading.remove wire:target="clockIn"></i>
                    Clock In
                </button>
            </div>

        @elseif($todayAttendance->clock_out)
            {{-- Clocked out --}}
            <div class="text-center mb-3">
                <span class="clock-status-badge bg-success-subtle text-success">Shift Complete</span>
            </div>
            <div class="d-flex justify-content-around text-center">
                <div>
                    <div class="text-muted small">Clock In</div>
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($todayAttendance->clock_in)->format('h:i A') }}</div>
                </div>
                <div>
                    <div class="text-muted small">Clock Out</div>
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($todayAttendance->clock_out)->format('h:i A') }}</div>
                </div>
                <div>
                    <div class="text-muted small">Duration</div>
                    <div class="fw-bold">
                        {{ floor($todayAttendance->duration_minutes / 60) }}h
                        {{ $todayAttendance->duration_minutes % 60 }}m
                    </div>
                </div>
            </div>

        @else
            {{-- Active shift --}}
            <div class="text-center mb-3">
                @if($onBreak)
                    <span class="clock-status-badge bg-warning-subtle text-warning">On Break</span>
                @else
                    <span class="clock-status-badge bg-primary-subtle text-primary">Working</span>
                @endif
            </div>

            <div class="d-flex justify-content-around text-center mb-3">
                <div>
                    <div class="text-muted small">Clocked In</div>
                    <div class="fw-bold">{{ \Carbon\Carbon::parse($todayAttendance->clock_in)->format('h:i A') }}</div>
                </div>
                <div>
                    <div class="text-muted small">Breaks</div>
                    <div class="fw-bold">{{ $todayAttendance->breaks->count() }}</div>
                </div>
                <div>
                    <div class="text-muted small">Status</div>
                    <div class="fw-bold text-capitalize">{{ $todayAttendance->status }}</div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-center flex-wrap">
                @if(!$onBreak)
                    <button wire:click="startBreak"
                            wire:loading.attr="disabled"
                            class="btn btn-outline-warning rounded-pill px-3">
                        <i class="bi bi-pause-circle me-1"></i> Break
                    </button>
                    <button wire:click="clockOut"
                            wire:loading.attr="disabled"
                            wire:confirm="Are you sure you want to clock out?"
                            class="btn btn-danger rounded-pill px-4">
                        <span wire:loading wire:target="clockOut" class="spinner-border spinner-border-sm me-1"></span>
                        <i class="bi bi-box-arrow-right me-1" wire:loading.remove wire:target="clockOut"></i>
                        Clock Out
                    </button>
                @else
                    <button wire:click="endBreak"
                            wire:loading.attr="disabled"
                            class="btn btn-warning rounded-pill px-4">
                        <i class="bi bi-play-circle me-1"></i> End Break
                    </button>
                @endif
            </div>
        @endif

        {{-- Correction toggle --}}
        <div class="text-center mt-3">
            <button wire:click="$toggle('showCorrectionForm')"
                    class="btn btn-link btn-sm text-muted text-decoration-none">
                <i class="bi bi-pencil-square me-1"></i>
                {{ $showCorrectionForm ? 'Hide' : 'Request Attendance Correction' }}
            </button>
        </div>

        @if($showCorrectionForm)
            <div class="border-top mt-3 pt-3">
                <h6 class="fw-bold small mb-3">Correction Request</h6>
                <div class="mb-2">
                    <label class="form-label small mb-1">Type</label>
                    <select wire:model="correctionType" class="form-select form-select-sm">
                        <option value="time_correction">Time Correction (In & Out)</option>
                        <option value="clock_in">Missing Clock In</option>
                        <option value="clock_out">Missing Clock Out</option>
                        <option value="full_day">Full Day Present</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1">Date</label>
                    <input type="date" wire:model="correctionDate"
                           max="{{ now()->format('Y-m-d') }}"
                           class="form-control form-control-sm">
                    @error('correctionDate') <div class="text-danger" style="font-size:.75rem;">{{ $message }}</div> @enderror
                </div>
                @if(in_array($correctionType, ['time_correction', 'clock_in', 'full_day']))
                    <div class="mb-2">
                        <label class="form-label small mb-1">Clock In</label>
                        <input type="time" wire:model="correctionClockIn" class="form-control form-control-sm">
                    </div>
                @endif
                @if(in_array($correctionType, ['time_correction', 'clock_out']))
                    <div class="mb-2">
                        <label class="form-label small mb-1">Clock Out</label>
                        <input type="time" wire:model="correctionClockOut" class="form-control form-control-sm">
                    </div>
                @endif
                <div class="mb-3">
                    <label class="form-label small mb-1">Reason</label>
                    <textarea wire:model="correctionReason" class="form-control form-control-sm" rows="2"
                              placeholder="Explain why the correction is needed..."></textarea>
                    @error('correctionReason') <div class="text-danger" style="font-size:.75rem;">{{ $message }}</div> @enderror
                </div>
                <button wire:click="submitCorrection"
                        wire:loading.attr="disabled"
                        class="btn btn-primary btn-sm rounded-pill px-4">
                    <span wire:loading wire:target="submitCorrection" class="spinner-border spinner-border-sm me-1"></span>
                    Submit Request
                </button>
            </div>
        @endif
    </div>

    <script>
        (function() {
            function updateClock() {
                const el = document.getElementById('liveClock');
                if (el) {
                    const now = new Date();
                    el.textContent = now.toLocaleTimeString('en-GB', { hour12: false });
                }
            }
            updateClock();
            setInterval(updateClock, 1000);
        })();
    </script>
</div>
