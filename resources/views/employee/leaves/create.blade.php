<x-admin-layout>
    <x-slot name="header">
        Apply for Leave
    </x-slot>

    <div class="card border-0 shadow-sm col-md-8 mx-auto">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('employee.leaves.store') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="leave_type" class="form-label fw-bold">Leave Type</label>
                    <select name="leave_type" id="leave_type" class="form-select">
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Casual Leave">Casual Leave</option>
                        <option value="Earned Leave">Earned Leave</option>
                    </select>
                    @error('leave_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="start_date" class="form-label fw-bold">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" required value="{{ old('start_date') }}">
                        @error('start_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label fw-bold">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" required value="{{ old('end_date') }}">
                        @error('end_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="reason" class="form-label fw-bold">Reason</label>
                    <textarea name="reason" id="reason" rows="4" class="form-control" required placeholder="Describe the reason for leave...">{{ old('reason') }}</textarea>
                    @error('reason') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-between pt-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        Submit Application
                    </button>
                    <a href="{{ route('employee.leaves.index') }}" class="btn btn-light rounded-pill px-4">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
