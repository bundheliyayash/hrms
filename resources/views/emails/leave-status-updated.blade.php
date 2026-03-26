@extends('emails.layout')

@section('content')
@php
  $approved = $leave->status === 'approved';
  $badgeClass = $approved ? 'badge-success' : 'badge-danger';
  $statusText = ucfirst($leave->status);
@endphp

<p class="greeting">Leave Request {{ $statusText }}</p>
<p style="color:#475569;font-size:.9rem;margin:0 0 20px;">
  Your leave request has been <strong>{{ strtolower($statusText) }}</strong> by
  <strong>{{ $handledBy->name }}</strong>.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Leave Type</span>
    <span class="info-value">
      <span class="badge badge-info">{{ ucfirst($leave->leave_type) }}</span>
    </span>
  </div>
  <div class="info-row">
    <span class="info-label">From</span>
    <span class="info-value">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">To</span>
    <span class="info-value">{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Decision</span>
    <span class="info-value"><span class="badge {{ $badgeClass }}">{{ $statusText }}</span></span>
  </div>
  @if($leave->admin_comment)
  <div class="info-row">
    <span class="info-label">Admin Remark</span>
    <span class="info-value" style="max-width:300px;text-align:right;color:{{ $approved ? '#059669' : '#dc2626' }}">
      {{ $leave->admin_comment }}
    </span>
  </div>
  @endif
  <div class="info-row">
    <span class="info-label">Handled By</span>
    <span class="info-value">{{ $handledBy->name }}</span>
  </div>
</div>

@if($approved)
  <p style="font-size:.875rem;color:#059669;background:#ecfdf5;padding:12px 16px;border-radius:8px;border-left:4px solid #10b981;">
    ✅ Your leave has been approved. Please ensure your responsibilities are covered during your absence.
  </p>
@else
  <p style="font-size:.875rem;color:#dc2626;background:#fef2f2;padding:12px 16px;border-radius:8px;border-left:4px solid #ef4444;">
    ❌ Your leave has been rejected. Please contact your manager for more information.
  </p>
@endif

<a href="{{ url('/employee/leaves') }}" class="btn {{ $approved ? 'btn-success' : '' }}">View My Leaves</a>
@endsection
