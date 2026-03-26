@extends('emails.layout')

@section('content')
<p class="greeting">Invoice from {{ config('app.name') }}</p>
<p style="color:#475569;font-size:.9rem;margin:0 0 20px;">
  Dear <strong>{{ $invoice->client->contact_person ?? $invoice->client->name }}</strong>,<br>
  Please find your invoice details below. Kindly review and process the payment by the due date.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Invoice Number</span>
    <span class="info-value" style="font-family:monospace;font-size:.95rem;">{{ $invoice->invoice_number }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Invoice Date</span>
    <span class="info-value">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Billing Period</span>
    <span class="info-value">
      {{ \Carbon\Carbon::parse($invoice->billing_period_start)->format('d M') }}
      –
      {{ \Carbon\Carbon::parse($invoice->billing_period_end)->format('d M Y') }}
    </span>
  </div>
  <div class="info-row">
    <span class="info-label">Total Worker-Days</span>
    <span class="info-value">{{ $invoice->total_worker_days }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Rate Per Day</span>
    <span class="info-value">₹{{ number_format($invoice->rate_per_day, 2) }}</span>
  </div>
  @if($invoice->tax_amount > 0)
  <div class="info-row">
    <span class="info-label">Tax ({{ $invoice->tax_percentage }}%)</span>
    <span class="info-value">₹{{ number_format($invoice->tax_amount, 2) }}</span>
  </div>
  @endif
  @if($invoice->discount_amount > 0)
  <div class="info-row">
    <span class="info-label">Discount</span>
    <span class="info-value" style="color:#dc2626;">–₹{{ number_format($invoice->discount_amount, 2) }}</span>
  </div>
  @endif
  <div class="info-row" style="border-top:2px solid #e2e8f0;margin-top:6px;padding-top:10px;">
    <span class="info-label" style="font-weight:700;color:#1e293b;font-size:.95rem;">Net Amount Due</span>
    <span class="info-value" style="font-size:1.1rem;color:#059669;">₹{{ number_format($invoice->net_amount, 2) }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Due Date</span>
    <span class="info-value" style="color:#d97706;font-weight:700;">
      {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}
    </span>
  </div>
</div>

<p style="font-size:.8rem;color:#94a3b8;background:#f8fafc;padding:12px 16px;border-radius:8px;margin:0;">
  For payment queries or disputes, please contact us at
  <strong>{{ config('mail.from.address') }}</strong>.
  Please reference your invoice number in all communications.
</p>
@endsection
