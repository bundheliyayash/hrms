<x-admin-layout>
    <x-slot name="header">Record Salary Advance</x-slot>

    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold">New Salary Advance</h5>
                    <p class="text-muted small mb-0">Record an advance payment for an employee. It will be auto-deducted from the selected payroll month.</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.salary-advances.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="user_id" class="form-label fw-bold">Employee <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">-- Select Employee --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ old('user_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->name }}
                                            @if($emp->employeeDetail)
                                                ({{ $emp->employeeDetail->employee_id }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="amount" class="form-label fw-bold">Advance Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="amount" id="amount"
                                        class="form-control @error('amount') is-invalid @enderror"
                                        value="{{ old('amount') }}" required min="1">
                                </div>
                                @error('amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="advance_date" class="form-label fw-bold">Advance Date <span class="text-danger">*</span></label>
                                <input type="date" name="advance_date" id="advance_date"
                                    class="form-control @error('advance_date') is-invalid @enderror"
                                    value="{{ old('advance_date', now()->format('Y-m-d')) }}" required>
                                @error('advance_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="recovery_month" class="form-label fw-bold">Deduct From Payroll Month <span class="text-danger">*</span></label>
                                <input type="month" name="recovery_month" id="recovery_month"
                                    class="form-control @error('recovery_month') is-invalid @enderror"
                                    value="{{ old('recovery_month', now()->format('Y-m')) }}" required>
                                <div class="form-text">The advance amount will be auto-filled in the payroll form for this month.</div>
                                @error('recovery_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="reason" class="form-label fw-bold">Reason / Notes</label>
                                <textarea name="reason" id="reason" rows="3"
                                    class="form-control @error('reason') is-invalid @enderror"
                                    placeholder="Medical emergency, personal need, etc.">{{ old('reason') }}</textarea>
                                @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.salary-advances.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5">
                                <i class="bi bi-check-circle me-1"></i> Record Advance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
