<x-admin-layout>
    <x-slot name="header">
        Invoice: {{ $invoice->invoice_number }}
    </x-slot>

    <div class="row justify-content-center mb-4">
        <div class="col-md-10">
            <!-- Action Toolbar -->
            <div class="card shadow-sm border-0 mb-4 no-print">
                <div class="card-body d-flex justify-content-between align-items-center py-2">
                    <div>
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-link text-muted text-decoration-none px-0"><i class="bi bi-arrow-left"></i> Back to List</a>
                    </div>
                    <div class="d-flex gap-2">
                        @if($invoice->status == 'draft')
                            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit Factors</a>
                            <form action="{{ route('admin.invoices.send', $invoice) }}" method="POST">@csrf<button class="btn btn-primary btn-sm"><i class="bi bi-send"></i> Finalize & Send</button></form>
                        @endif
                        <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-file-earmark-pdf"></i> Download PDF</a>
                        <button onclick="window.print()" class="btn btn-secondary btn-sm"><i class="bi bi-printer"></i> Print</button>
                    </div>
                </div>
            </div>

            <!-- Main Invoice Body -->
            <div class="card shadow border-0 invoice-body bg-white p-5 rounded-0">
                <div class="row mb-5 border-bottom pb-4">
                    <div class="col-6">
                        <h2 class="fw-bold text-primary mb-1">CLEAN SHEEN</h2>
                        <p class="text-muted small">Quality Cleaning Services<br>HQ: Sector 5, Business Park<br>Mumbai, MH - 400001</p>
                    </div>
                    <div class="col-6 text-end">
                        <h1 class="display-6 fw-bold text-uppercase opacity-25">Invoice</h1>
                        <h4 class="mb-0">{{ $invoice->invoice_number }}</h4>
                        <p class="text-muted small">Date: {{ $invoice->invoice_date->format('d M, Y') }}</p>
                        
                        <div class="mt-3">
                            <span class="badge {{ $invoice->status == 'paid' ? 'bg-success' : 'bg-warning' }} px-3 py-2 fs-6">
                                STATUS: {{ strtoupper($invoice->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-6">
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Bill To:</h6>
                        <h5 class="fw-bold mb-1">{{ $invoice->client->name }}</h5>
                        <p class="text-muted small">Contact Person: {{ $invoice->client->contact_person }}<br>
                        Email: {{ $invoice->client->email }}<br>
                        Phone: {{ $invoice->client->phone }}</p>
                    </div>
                    <div class="col-3">
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Contract Ref:</h6>
                        <p class="fw-bold mb-0">#{{ $invoice->contract->contract_number }}</p>
                        <p class="small text-muted">{{ ucfirst($invoice->contract->contract_type) }} Contract</p>
                    </div>
                    <div class="col-3 text-end">
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Due Date:</h6>
                        <h5 class="fw-bold text-danger">{{ $invoice->due_date->format('d M, Y') }}</h5>
                        <p class="small text-muted">Terms: {{ ucfirst($invoice->contract->payment_terms) }}</p>
                    </div>
                </div>

                <div class="bg-light p-3 mb-4 rounded d-flex justify-content-between">
                    <div><strong>Billing Period:</strong> {{ $invoice->billing_period_start->format('d M, Y') }} to {{ $invoice->billing_period_end->format('d M, Y') }}</div>
                    <div><strong>Service Rate:</strong> ₹{{ number_format($invoice->rate_per_day, 2) }} / day</div>
                </div>

                <div class="table-responsive mb-5">
                    <table class="table align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">Date</th>
                                <th>Worker Name / Site</th>
                                <th class="text-center">Shift Hours</th>
                                <th class="text-center">Units (Days)</th>
                                <th class="text-end pe-3">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->lineItems as $item)
                            <tr>
                                <td class="ps-3 small">{{ $item->service_date->format('d/m/Y') }}</td>
                                <td>
                                    <div class="fw-bold">{{ $item->worker_name }}</div>
                                    <small class="text-muted">{{ $item->site->site_name }}</small>
                                </td>
                                <td class="text-center">{{ number_format($item->hours_worked, 1) }}h</td>
                                <td class="text-center">1.0</td>
                                <td class="text-end pe-3 fw-bold">₹{{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end ps-3">Total Worker Days</td>
                                <td class="text-center">{{ $invoice->total_worker_days }}</td>
                                <td class="text-end pe-3">₹{{ number_format($invoice->gross_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted">Gross Amount:</td>
                                <td class="text-end fw-bold">₹{{ number_format($invoice->gross_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Discount / Adj:</td>
                                <td class="text-end fw-bold text-danger">- ₹{{ number_format($invoice->discount_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">GST ({{ $invoice->tax_percentage }}%):</td>
                                <td class="text-end fw-bold">₹{{ number_format($invoice->tax_amount, 2) }}</td>
                            </tr>
                            <tr class="border-top">
                                <td class="fs-4 fw-bold pt-3">NET TOTAL:</td>
                                <td class="fs-4 fw-bold pt-3 text-end text-primary">₹{{ number_format($invoice->net_amount, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($invoice->status == 'paid')
                <div class="mt-5 p-4 border rounded bg-light border-success border-2">
                    <div class="row">
                        <div class="col-1"><i class="bi bi-check-circle-fill text-success fs-1"></i></div>
                        <div class="col-11">
                            <h5 class="fw-bold text-success mb-1">PAYMENT RECEIVED</h5>
                            <p class="mb-0 small text-muted">Paid on <strong>{{ $invoice->payment_date->format('d M, Y') }}</strong> via <strong>{{ $invoice->payment_method }}</strong>. Reference: {{ $invoice->payment_reference ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-5 pt-5 border-top">
                    <h6 class="fw-bold mb-2 small text-uppercase text-muted">Instructions & Notes:</h6>
                    <div class="small text-muted mb-4">{{ $invoice->notes ?? 'Standard payment terms apply. Please quote invoice number during bank transfer.' }}</div>
                    
                    <div class="row mt-5">
                        <div class="col-6">
                            <div class="small text-muted border-top border-dark pt-2 mt-4" style="width: 200px">Authorized Signatory</div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="fs-6 fw-bold">CLEAN SHEEN HQ</div>
                            <div class="small text-muted">Verified & Sealed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .invoice-body { font-family: 'Inter', sans-serif; letter-spacing: -0.01em; }
        @media print {
            .no-print { display: none !important; }
            .navbar, .sidebar, .header-container { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            .invoice-body { box-shadow: none !important; width: 100% !important; }
            body { background: white !important; }
        }
    </style>
</x-admin-layout>
