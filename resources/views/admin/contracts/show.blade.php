<x-admin-layout>
    <x-slot name="header">
        Contract Details: {{ $contract->contract_number }}
    </x-slot>

    <div class="row">
        <!-- Sidebar: Stats & Status -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        @if($contract->status == 'active')
                            <span class="badge bg-success px-4 py-2 fs-6">ACTIVE</span>
                        @elseif($contract->status == 'draft')
                            <span class="badge bg-primary px-4 py-2 fs-6">DRAFT</span>
                        @elseif($contract->status == 'completed')
                            <span class="badge bg-secondary px-4 py-2 fs-6">COMPLETED</span>
                        @else
                            <span class="badge bg-danger px-4 py-2 fs-6">{{ strtoupper($contract->status) }}</span>
                        @endif
                    </div>
                    <h5 class="card-title mb-1 text-primary">{{ $contract->client->name }}</h5>
                    <p class="text-muted small mb-3">Contract #{{ $contract->contract_number }}</p>
                    
                    <div class="d-grid gap-2">
                        @if($contract->status == 'draft')
                        <form action="{{ route('admin.contracts.toggle-status', $contract) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="btn btn-success w-100">Activate Contract</button>
                        </form>
                        @endif
                        
                        <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-outline-primary {{ in_array($contract->status, ['completed', 'cancelled']) ? 'disabled' : '' }}">
                            <i class="bi bi-pencil me-1"></i> Edit Details
                        </a>
                        
                        <a href="{{ route('admin.invoices.generate-view', ['contract_id' => $contract->id]) }}" class="btn btn-info text-white">
                            <i class="bi bi-receipt me-1"></i> Generate Invoice
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white font-weight-bold">Summary Stats</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Type:</span>
                            <span class="fw-bold">{{ ucfirst($contract->contract_type) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Start Date:</span>
                            <span class="fw-bold">{{ $contract->start_date->format('d M, Y') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">End Date:</span>
                            <span class="fw-bold">{{ $contract->end_date ? $contract->end_date->format('d M, Y') : 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Rate/Day:</span>
                            <span class="fw-bold text-success">₹{{ number_format($contract->rate_per_day, 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Required Workers:</span>
                            <span class="badge bg-dark rounded-pill">{{ $contract->minimum_workers_required }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            @if($contract->terms_and_conditions)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">Notes / Terms</div>
                <div class="card-body small">
                    {{ $contract->terms_and_conditions }}
                </div>
            </div>
            @endif
        </div>

        <!-- Main Content: Sites, Assignments, Invoices -->
        <div class="col-md-8">
            <!-- Linked Sites -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-geo-alt me-2 text-primary"></i>Covered Sites</h6>
                    <span class="badge bg-light text-dark">{{ $contract->sites->count() }} Sites</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 py-2">Site Name</th>
                                <th>Location</th>
                                <th>Workers Required</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contract->sites as $site)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $site->site_name }}</td>
                                <td class="small">{{ $site->location }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $site->pivot->workers_required }} workers</span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.sites.edit', $site) }}" class="btn btn-link btn-sm p-0 text-decoration-none">View Site</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Daily Assignments -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-success"></i>Recent Deployments</h6>
                    <a href="{{ route('admin.assignments.index', ['contract_id' => $contract->id]) }}" class="small text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Date</th>
                                <th>Employee</th>
                                <th>Site</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contract->dailyAssignments as $assignment)
                            <tr>
                                <td class="ps-3 small">{{ $assignment->assigned_date->format('d/m/Y') }}</td>
                                <td class="fw-bold">{{ $assignment->employee->name }}</td>
                                <td class="small">{{ $assignment->site->site_name }}</td>
                                <td>
                                    <span class="badge rounded-pill bg-light text-dark border small">{{ ucfirst($assignment->status) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted small">No assignments recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-receipt me-2 text-info"></i>Billing History</h6>
                    <a href="{{ route('admin.invoices.index', ['contract_id' => $contract->id]) }}" class="small text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Invoice #</th>
                                <th>Period</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contract->invoices as $invoice)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $invoice->invoice_number }}</td>
                                <td class="small">{{ $invoice->billing_period_start->format('M d') }} - {{ $invoice->billing_period_end->format('M d') }}</td>
                                <td class="fw-bold text-success">₹{{ number_format($invoice->net_amount, 2) }}</td>
                                <td>
                                    <span class="badge {{ $invoice->status == 'paid' ? 'bg-success' : 'bg-warning' }}">{{ ucfirst($invoice->status) }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-3 text-muted small">No invoices generated yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
