<x-admin-layout>
    <x-slot name="header">
        Client Billing & Invoicing
    </x-slot>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body">
                    <h6 class="opacity-75 mb-1">Total Invoiced</h6>
                    <h3 class="mb-0">₹{{ number_format(\App\Models\ClientInvoice::where('status', '!=', 'cancelled')->sum('net_amount'), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body">
                    <h6 class="opacity-75 mb-1">Total Received</h6>
                    <h3 class="mb-0">₹{{ number_format(\App\Models\ClientInvoice::where('status', 'paid')->sum('net_amount'), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-warning text-dark">
                <div class="card-body">
                    <h6 class="opacity-75 mb-1">Outstanding (Sent)</h6>
                    <h3 class="mb-0">₹{{ number_format(\App\Models\ClientInvoice::where('status', 'sent')->sum('net_amount'), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-danger text-white">
                <div class="card-body">
                    <h6 class="opacity-75 mb-1">Overdue Amount</h6>
                    <h3 class="mb-0">₹{{ number_format(\App\Models\ClientInvoice::overdue()->sum('net_amount'), 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-muted">Invoice History</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.invoices.overdue') }}" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-exclamation-octagon"></i> Overdue Alerts
                </a>
                <a href="{{ route('admin.invoices.generate-view') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-lightning-charge"></i> Generate New Invoice
                </a>
            </div>
        </div>
        
        <div class="card-body border-bottom bg-light">
            <form action="{{ route('admin.invoices.index') }}" method="GET" class="row g-2">
                <div class="col-md-4">
                    <select name="client_id" class="form-select form-select-sm">
                        <option value="">All Clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="date_range" class="form-control form-control-sm" placeholder="Date Range (YYYY-MM-DD - YYYY-MM-DD)" value="{{ request('date_range') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bi bi-funnel"></i> Filter</button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Invoice #</th>
                            <th>Client / Contract</th>
                            <th>Billing Period</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        <tr>
                            <td class="ps-3 fw-bold">{{ $invoice->invoice_number }}</td>
                            <td>
                                <div class="fw-bold">{{ $invoice->client->name }}</div>
                                <small class="text-muted">Contract: {{ $invoice->contract->contract_number }}</small>
                            </td>
                            <td>
                                <div class="small">{{ $invoice->billing_period_start->format('d M') }} - {{ $invoice->billing_period_end->format('d M, Y') }}</div>
                                <small class="text-success badge bg-light text-success border">{{ $invoice->total_worker_days }} Worker Days</small>
                            </td>
                            <td>
                                <div class="fw-bold">₹{{ number_format($invoice->net_amount, 2) }}</div>
                                <small class="text-muted">Tax: ₹{{ number_format($invoice->tax_amount, 2) }}</small>
                            </td>
                            <td>
                                @php $isDueSoon = now()->diffInDays($invoice->due_date, false) <= 3 && $invoice->status != 'paid'; @endphp
                                <span class="{{ $isDueSoon ? 'text-danger fw-bold' : '' }}">
                                    {{ $invoice->due_date->format('d M, Y') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $invoice->status == 'paid' ? 'bg-success' : ($invoice->status == 'sent' ? 'bg-info text-dark' : ($invoice->status == 'overdue' ? 'bg-danger' : 'bg-secondary')) }}">
                                    {{ strtoupper($invoice->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-dark" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                
                                <div class="dropdown d-inline">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        More
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        @if($invoice->status == 'draft')
                                            <li><form action="{{ route('admin.invoices.send', $invoice) }}" method="POST">@csrf<button type="submit" class="dropdown-item"><i class="bi bi-send me-2 text-primary"></i> Mark as Sent</button></form></li>
                                            <li><a class="dropdown-item" href="{{ route('admin.invoices.edit', $invoice) }}"><i class="bi bi-pencil me-2"></i> Edit Factors</a></li>
                                        @endif
                                        @if($invoice->status != 'paid' && $invoice->status != 'cancelled')
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $invoice->id }}"><i class="bi bi-currency-dollar me-2 text-success"></i> Record Payment</a></li>
                                        @endif
                                        @if($invoice->status == 'draft')
                                            <li><hr class="dropdown-divider"></li>
                                            <li><form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST" data-confirm-delete="true" data-delete-message="Delete this draft?">@csrf @method('DELETE')<button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> Delete Draft</button></form></li>
                                        @endif
                                    </ul>
                                </div>

                                <!-- Payment Modal -->
                                <div class="modal fade" id="paymentModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content text-start">
                                            <form action="{{ route('admin.invoices.mark-paid', $invoice) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Record Payment: {{ $invoice->invoice_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                                                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Amount Due</label>
                                                            <div class="form-control bg-light">₹{{ number_format($invoice->net_amount, 2) }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                                        <select name="payment_method" class="form-select" required>
                                                            <option value="NEFT/Bank Transfer">NEFT/Bank Transfer</option>
                                                            <option value="Cheque">Cheque</option>
                                                            <option value="Cash">Cash</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Reference Number (UTR/Cheque #)</label>
                                                        <input type="text" name="payment_reference" class="form-control" placeholder="Optional">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-success">Complete Payment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No invoices found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $invoices->links() }}
        </div>
    </div>
</x-admin-layout>
