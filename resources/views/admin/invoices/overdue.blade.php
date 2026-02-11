<x-admin-layout>
    <x-slot name="header">
        Overdue Payment Alerts
    </x-slot>

    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4 py-3">
        <i class="bi bi-clock-history fs-4 me-3"></i>
        <div>
            <strong>Payment Delays Detected!</strong> The following invoices have passed their due date without receiving payment. Immediate follow-up or legal notice may be required.
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted">List of Overdue Bills</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Invoice #</th>
                            <th>Client / Contact</th>
                            <th>Due Date</th>
                            <th>Amount Due</th>
                            <th>Days Overdue</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        @php $daysOverdue = now()->diffInDays($invoice->due_date, false); @endphp
                        <tr>
                            <td class="ps-3 fw-bold">{{ $invoice->invoice_number }}</td>
                            <td>
                                <div class="fw-bold">{{ $invoice->client->name }}</div>
                                <small class="text-muted">{{ $invoice->client->contact_person }} ({{ $invoice->client->phone }})</small>
                            </td>
                            <td class="text-danger fw-bold">{{ $invoice->due_date->format('d M, Y') }}</td>
                            <td class="fw-bold text-primary">₹{{ number_format($invoice->net_amount, 2) }}</td>
                            <td>
                                <span class="badge bg-danger rounded-pill px-3 py-2">
                                    {{ abs($daysOverdue) }} Days Late
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-info">View Details</a>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-danger dropdown-toggle" data-bs-toggle="dropdown">Follow Up</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="tel:{{ $invoice->client->phone }}"><i class="bi bi-telephone me-2"></i> Call Client</a></li>
                                        <li><a class="dropdown-item" href="mailto:{{ $invoice->client->email }}?subject=Urgent: Overdue Payment {{ $invoice->invoice_number }}"><i class="bi bi-envelope me-2"></i> Send Email Reminder</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $invoice->id }}"><i class="bi bi-check-circle me-2"></i> Record Payment</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-shield-check text-success display-4"></i>
                                <p class="mt-3 text-muted h5">No overdue payments found. Your collections are on track!</p>
                            </td>
                        </tr>
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
