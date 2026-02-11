<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <span>My Leaves</span>
            <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="openLeaveSidebar()">
                <i class="bi bi-plus-circle me-1"></i> Apply Leave
            </button>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Leave Balance Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-calendar-check text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Sick Leave</h6>
                            <h4 class="mb-0 fw-bold">10 <small class="text-muted fs-6">days</small></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-umbrella text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Casual Leave</h6>
                            <h4 class="mb-0 fw-bold">12 <small class="text-muted fs-6">days</small></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-star text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Earned Leave</h6>
                            <h4 class="mb-0 fw-bold">15 <small class="text-muted fs-6">days</small></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-calendar-x text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1 small">Total Used</h6>
                            <h4 class="mb-0 fw-bold">{{ $leaves->where('status', 'approved')->count() }} <small class="text-muted fs-6">days</small></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        @foreach($allocations as $alloc)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted fw-bold">{{ $alloc->leave_type }}</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $alloc->total_days - $alloc->used_days }} Left</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: {{ ($alloc->used_days / $alloc->total_days) * 100 }}%"></div>
                    </div>
                    <div class="mt-2 small text-muted">
                        {{ (float)$alloc->used_days }} used of {{ (float)$alloc->total_days }} days
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Main Content -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Leave Application History</h5>
            <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="offcanvas" data-bs-target="#applyLeaveSidebar">
                <i class="bi bi-plus-lg me-2"></i>Apply for Leave
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="px-4 py-3">Leave Type</th>
                            <th class="py-3">Period</th>
                            <th class="py-3">Duration</th>
                            <th class="py-3">Reason</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end px-4">Admin Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                        <tr>
                            <td class="px-4">
                                <span class="fw-bold text-dark">{{ $leave->leave_type }}</span>
                                @if($leave->is_half_day)
                                    <span class="badge bg-info-subtle text-info small ms-1">Half Day</span>
                                @endif
                            </td>
                            <td>
                                <div class="small fw-bold">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('d M, Y') }}</div>
                                <div class="text-muted extra-small">Applied on {{ $leave->created_at->format('d M') }}</div>
                            </td>
                            <td>
                                @php 
                                    $start = \Carbon\Carbon::parse($leave->start_date);
                                    $end = \Carbon\Carbon::parse($leave->end_date);
                                    $days = $leave->is_half_day ? 0.5 : ($start->diffInDays($end) + 1);
                                @endphp
                                <span class="badge bg-light text-dark fw-normal border">{{ $days }} Day(s)</span>
                            </td>
                            <td><div class="text-truncate" style="max-width: 150px;" title="{{ $leave->reason }}">{{ $leave->reason }}</div></td>
                            <td>
                                @if($leave->status === 'approved')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Approved</span>
                                @elseif($leave->status === 'rejected')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Rejected</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3">Pending</span>
                                @endif
                            </td>
                            <td class="text-end px-4 small italic text-muted">
                                {{ $leave->admin_comment ?? 'No remarks' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No leave applications found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $leaves->links() }}
        </div>
    </div>

    <!-- Apply Leave Offcanvas -->
    <div class="offcanvas offcanvas-end border-0 shadow" tabindex="-1" id="applyLeaveSidebar" style="width: 400px;">
        <div class="offcanvas-header bg-primary text-white p-4">
            <h5 class="offcanvas-title fw-bold">Apply for Leave</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-4">
            <form action="{{ route('employee.leaves.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase tracking-wider">Leave Type</label>
                    <select name="leave_type" class="form-select border-0 bg-light p-3" required>
                        <option value="">Select leave type</option>
                        <option value="Annual Leave">Annual Leave</option>
                        <option value="Sick Leave">Sick Leave</option>
                        <option value="Casual Leave">Casual Leave</option>
                        <option value="Earned Leave">Earned Leave</option>
                        <option value="Unpaid Leave">Unpaid Leave</option>
                    </select>
                </div>

                <!-- Half Day Toggle -->
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="halfDayToggle" name="is_half_day" value="1" onchange="toggleHalfDay()">
                        <label class="form-check-label fw-bold" for="halfDayToggle">
                            Half Day Leave
                        </label>
                    </div>
                    <small class="text-muted">Check this if you need only half day leave</small>
                </div>

                <!-- Date Selection -->
                <div id="fullDayDates">
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label fw-bold small">From Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="startDate" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">To Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="endDate" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div id="halfDayDate" class="d-none mb-4">
                    <label class="form-label fw-bold small">Leave Date <span class="text-danger">*</span></label>
                    <input type="date" name="half_day_date" id="halfDayDateInput" class="form-control">
                    
                    <label class="form-label fw-bold small mt-3">Half Day Period <span class="text-danger">*</span></label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="half_day_period" id="morning" value="morning">
                        <label class="btn btn-outline-primary" for="morning">
                            <i class="bi bi-sunrise me-1"></i> Morning
                        </label>
                        
                        <input type="radio" class="btn-check" name="half_day_period" id="afternoon" value="afternoon">
                        <label class="btn btn-outline-primary" for="afternoon">
                            <i class="bi bi-sunset me-1"></i> Afternoon
                        </label>
                    </div>
                </div>

                <!-- Reason -->
                <div class="mb-4">
                    <label class="form-label fw-bold small">Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="4" placeholder="Please provide a reason for your leave..." required></textarea>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill py-2">
                        <i class="bi bi-send me-2"></i> Submit Leave Request
                    </button>
                    <button type="button" class="btn btn-light rounded-pill py-2" data-bs-dismiss="offcanvas">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openLeaveSidebar() {
            const sidebar = new bootstrap.Offcanvas(document.getElementById('leaveSidebar'));
            sidebar.show();
        }

        function toggleHalfDay() {
            const halfDayToggle = document.getElementById('halfDayToggle');
            const fullDayDates = document.getElementById('fullDayDates');
            const halfDayDate = document.getElementById('halfDayDate');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const halfDayDateInput = document.getElementById('halfDayDateInput');

            if (halfDayToggle.checked) {
                fullDayDates.classList.add('d-none');
                halfDayDate.classList.remove('d-none');
                startDate.removeAttribute('required');
                endDate.removeAttribute('required');
                halfDayDateInput.setAttribute('required', 'required');
            } else {
                fullDayDates.classList.remove('d-none');
                halfDayDate.classList.add('d-none');
                startDate.setAttribute('required', 'required');
                endDate.setAttribute('required', 'required');
                halfDayDateInput.removeAttribute('required');
            }
        }

        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('startDate').setAttribute('min', today);
            document.getElementById('endDate').setAttribute('min', today);
            document.getElementById('halfDayDateInput').setAttribute('min', today);
        });
    </script>
</x-admin-layout>
