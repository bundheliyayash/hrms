<x-admin-layout>
    <x-slot name="header">
        System Settings
    </x-slot>

    <div class="py-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 2rem; z-index: 10;">
                    <div class="card-body p-0">
                        <div class="nav flex-column nav-pills rounded-3" id="settingsTabs" role="tablist">
                            <button class="nav-link active text-start border-0 px-4 py-3" data-bs-toggle="pill" data-bs-target="#profile-perms" type="button" role="tab">
                                <i class="bi bi-person-gear me-2"></i> Profile Permissions
                            </button>
                            <button class="nav-link text-start border-0 px-4 py-3" data-bs-toggle="pill" data-bs-target="#general-settings" type="button" role="tab">
                                <i class="bi bi-gear me-2"></i> General Settings
                            </button>
                            <button class="nav-link text-start border-0 px-4 py-3" data-bs-toggle="pill" data-bs-target="#notification-config" type="button" role="tab">
                                <i class="bi bi-bell me-2"></i> Notification Config
                            </button>
                            <button class="nav-link text-start border-0 px-4 py-3" data-bs-toggle="pill" data-bs-target="#payroll-config" type="button" role="tab">
                                <i class="bi bi-calculator me-2"></i> Payroll Config
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm mb-4 alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="tab-content" id="settingsTabsContent">
                    <!-- Profile Permissions Tab -->
                    <div class="tab-pane fade show active" id="profile-perms" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="mb-0 fw-bold">Employee Profile Field Permissions</h5>
                                <p class="text-muted small mb-0">Control which fields employees are allowed to edit from their "My Profile" page.</p>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.settings.update-profile-permissions') }}" method="POST">
                                    @csrf
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="border-0 px-4 py-3">Field Name</th>
                                                    <th class="border-0 py-3 text-center">Allow Employee to Edit</th>
                                                    <th class="border-0 py-3">Description</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($profileFields as $key => $label)
                                                <tr>
                                                    <td class="px-4 fw-bold text-dark">{{ $label }}</td>
                                                    <td class="text-center">
                                                        <div class="form-check form-switch d-inline-block">
                                                            <input class="form-check-input" type="checkbox" name="fields[{{ $key }}]" id="switch_{{ $key }}" {{ $permissions[$key] ? 'checked' : '' }}>
                                                        </div>
                                                    </td>
                                                    <td class="small text-muted">
                                                        @if($permissions[$key])
                                                            <span class="text-success"><i class="bi bi-check-circle-fill"></i> Employee can update their own {{ strtolower($label) }}</span>
                                                        @else
                                                            <span class="text-secondary"><i class="bi bi-lock-fill"></i> Read-only for employee (Admin only)</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                            Save Permissions
                                        </button> 
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- General Settings Tab -->
                    <div class="tab-pane fade" id="general-settings" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="mb-0 fw-bold">General System Settings</h5>
                                <p class="text-muted small mb-0">Configure basic application information.</p>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.settings.update-general') }}" method="POST">
                                    @csrf
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Application Name</label>
                                            <input type="text" name="app_name" class="form-control" value="{{ $generalSettings['app_name'] }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Company Name</label>
                                            <input type="text" name="company_name" class="form-control" value="{{ $generalSettings['company_name'] }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Footer Text</label>
                                            <input type="text" name="footer_text" class="form-control" value="{{ $generalSettings['footer_text'] }}">
                                        </div>
                                    </div>
                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                            Update General Settings
                                        </button> 
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Config Tab -->
                    <div class="tab-pane fade" id="notification-config" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="mb-0 fw-bold">Notification Configurations</h5>
                                <p class="text-muted small mb-0">Enable or disable automated system notifications.</p>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.settings.update-notifications') }}" method="POST">
                                    @csrf
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                            <div>
                                                <h6 class="mb-0 fw-bold">Attendance Notifications</h6>
                                                <small class="text-muted">Notify admins/managers about clock-ins and late arrivals.</small>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="notify_attendance" value="1" {{ $notifConfig['notify_attendance'] ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                            <div>
                                                <h6 class="mb-0 fw-bold">Leave Notifications</h6>
                                                <small class="text-muted">Notify users about leave approval status or new requests.</small>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="notify_leaves" value="1" {{ $notifConfig['notify_leaves'] ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-0">
                                            <div>
                                                <h6 class="mb-0 fw-bold">Payroll Notifications</h6>
                                                <small class="text-muted">Notify employees when their payslip is generated.</small>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="notify_payroll" value="1" {{ $notifConfig['notify_payroll'] ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                            Save Notification Config
                                        </button> 
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Payroll Config Tab -->
                    <div class="tab-pane fade" id="payroll-config" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="mb-0 fw-bold">Payroll Configuration</h5>
                                <p class="text-muted small mb-0">All percentages and amounts used across payroll, payslips, exports and invoices are controlled here.</p>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.settings.update-payroll-config') }}" method="POST">
                                    @csrf

                                    {{-- Statutory Deductions --}}
                                    <h6 class="text-uppercase text-muted small fw-bold mb-3 mt-2">
                                        <i class="bi bi-building me-1"></i> Statutory Deductions (Employee)
                                    </h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">PF Employee % <small class="text-muted">(of Basic)</small></label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" name="pf_percentage" class="form-control" value="{{ $payrollConfig['pf_percentage'] }}" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-text">Standard: 12%</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">ESI Employee % <small class="text-muted">(of Gross)</small></label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" name="esi_percentage" class="form-control" value="{{ $payrollConfig['esi_percentage'] }}" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-text">Standard: 0.75%</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Professional Tax <small class="text-muted">(flat/month)</small></label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" name="pt_amount" class="form-control" value="{{ $payrollConfig['pt_amount'] }}" required>
                                            </div>
                                            <div class="form-text">Maharashtra: ₹200, varies by state</div>
                                        </div>
                                    </div>

                                    {{-- Employer Contributions --}}
                                    <h6 class="text-uppercase text-muted small fw-bold mb-3">
                                        <i class="bi bi-briefcase me-1"></i> Employer Contributions <small>(shown in Excel export / CTC)</small>
                                    </h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">PF Employer % <small class="text-muted">(of Basic)</small></label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" name="pf_employer_percentage" class="form-control" value="{{ $payrollConfig['pf_employer_percentage'] }}" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-text">Standard: 13%</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">ESI Employer % <small class="text-muted">(of Gross)</small></label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" name="esi_employer_percentage" class="form-control" value="{{ $payrollConfig['esi_employer_percentage'] }}" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-text">Standard: 3.25%</div>
                                        </div>
                                    </div>

                                    {{-- Allowances --}}
                                    <h6 class="text-uppercase text-muted small fw-bold mb-3">
                                        <i class="bi bi-plus-circle me-1"></i> Default Allowances (Auto-fill in Payroll Form)
                                    </h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">HRA % <small class="text-muted">(of Basic)</small></label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" name="hra_percentage" class="form-control" value="{{ $payrollConfig['hra_percentage'] }}" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-text">Used as default HRA in payroll form</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Washing Allowance % <small class="text-muted">(of Basic)</small></label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" name="washing_allowance_percentage" class="form-control" value="{{ $payrollConfig['washing_allowance_percentage'] }}" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-text">Used as default washing allowance</div>
                                        </div>
                                    </div>

                                    {{-- Billing --}}
                                    <h6 class="text-uppercase text-muted small fw-bold mb-3">
                                        <i class="bi bi-receipt me-1"></i> Billing / Invoice
                                    </h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Invoice GST %</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" name="gst_percentage" class="form-control" value="{{ $payrollConfig['gst_percentage'] }}" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-text">Applied as default tax on new invoices</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Working Hours / Day</label>
                                            <div class="input-group">
                                                <input type="number" step="1" name="working_hours_per_day" class="form-control" value="{{ $payrollConfig['working_hours_per_day'] }}" required>
                                                <span class="input-group-text">hrs</span>
                                            </div>
                                            <div class="form-text">Used for OT rate calc &amp; per-service invoice billing</div>
                                        </div>
                                    </div>

                                    <div class="mt-2 text-end">
                                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                            <i class="bi bi-save me-1"></i> Save Payroll Config
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
