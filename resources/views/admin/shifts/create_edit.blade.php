<x-admin-layout>
    <x-slot name="header">
        {{ isset($shift) ? 'Edit Shift' : 'Create Shift' }}
    </x-slot>

    <div class="py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="mb-0 fw-bold">{{ isset($shift) ? 'Modify Shift Details' : 'New Shift Details' }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ isset($shift) ? route('admin.shifts.update', $shift) : route('admin.shifts.store') }}" method="POST">
                            @csrf
                            @if(isset($shift))
                                @method('PUT')
                            @endif

                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Shift Name</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $shift->name ?? '') }}" placeholder="e.g. Day Shift, Night Shift" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Clock In Time</label>
                                    <input type="time" name="clock_in_time" class="form-control @error('clock_in_time') is-invalid @enderror" 
                                           value="{{ old('clock_in_time', isset($shift) ? \Carbon\Carbon::parse($shift->clock_in_time)->format('H:i') : '09:00') }}" required>
                                    @error('clock_in_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Clock Out Time</label>
                                    <input type="time" name="clock_out_time" class="form-control @error('clock_out_time') is-invalid @enderror" 
                                           value="{{ old('clock_out_time', isset($shift) ? \Carbon\Carbon::parse($shift->clock_out_time)->format('H:i') : '18:00') }}" required>
                                    @error('clock_out_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Late Threshold (Min)</label>
                                    <div class="input-group">
                                        <input type="number" name="late_threshold_minutes" class="form-control @error('late_threshold_minutes') is-invalid @enderror" 
                                               value="{{ old('late_threshold_minutes', $shift->late_threshold_minutes ?? 15) }}" min="0" required>
                                        <span class="input-group-text">mins</span>
                                    </div>
                                    <small class="text-muted">Grace period after clock-in time</small>
                                    @error('late_threshold_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Early Out Threshold (Min)</label>
                                    <div class="input-group">
                                        <input type="number" name="early_out_threshold_minutes" class="form-control @error('early_out_threshold_minutes') is-invalid @enderror" 
                                               value="{{ old('early_out_threshold_minutes', $shift->early_out_threshold_minutes ?? 15) }}" min="0" required>
                                        <span class="input-group-text">mins</span>
                                    </div>
                                    <small class="text-muted">Grace period before clock-out time</small>
                                    @error('early_out_threshold_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Break Duration (Min)</label>
                                    <div class="input-group">
                                        <input type="number" name="break_duration_minutes" class="form-control @error('break_duration_minutes') is-invalid @enderror" 
                                               value="{{ old('break_duration_minutes', $shift->break_duration_minutes ?? 60) }}" min="0" required>
                                        <span class="input-group-text">mins</span>
                                    </div>
                                    <small class="text-muted">Standard break time deducted</small>
                                    @error('break_duration_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" 
                                               {{ old('is_active', $shift->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="is_active">Shift is Active</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 text-end">
                                <a href="{{ route('admin.shifts.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                    <i class="bi bi-save me-2"></i>{{ isset($shift) ? 'Update Shift' : 'Create Shift' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
