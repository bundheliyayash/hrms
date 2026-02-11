<x-admin-layout>
    <x-slot name="header">
        Edit Invoice Draft: {{ $invoice->invoice_number }}
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 text-muted">Adjust Billing Factors</h5>
                </div>
                <div class="card-body py-4">
                    <form action="{{ route('admin.invoices.update', $invoice) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4 text-center bg-light p-3 rounded">
                            <div class="text-muted small">Current Total Worker Days:</div>
                            <div class="h3 fw-bold mb-0 text-primary">{{ $invoice->total_worker_days }} Days</div>
                            <div class="text-muted small">At rate of ₹{{ number_format($invoice->rate_per_day, 2) }} / day</div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control" value="{{ $invoice->due_date->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tax Percentage (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="tax_percentage" class="form-control" value="{{ $invoice->tax_percentage }}" required>
                                    <span class="input-group-text">% GST</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Manual Discount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" name="discount_amount" class="form-control" value="{{ $invoice->discount_amount }}" required>
                                </div>
                                <small class="text-muted italic">Used for corrections or client favors.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Current Base Amount</label>
                                <div class="form-control bg-light">₹{{ number_format($invoice->gross_amount, 2) }}</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Invoice Notes / Payment Instructions</label>
                            <textarea name="notes" class="form-control" rows="4">{{ $invoice->notes }}</textarea>
                            <small class="text-muted">These notes will appear on the final PDF.</small>
                        </div>

                        <div class="d-flex justify-content-between pt-3">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-light border">Cancel Edits</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold">Update Invoice Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
