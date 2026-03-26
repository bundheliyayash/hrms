@extends('emails.layout')

@section('content')
@php
  $approved = $correction->status === 'approved';
  $badgeClass = $approved ? 'badge-success' : 'badge-danger';
  $statusText = ucfirst($correction->status);
@endphp

<p class="greeting">Attendance Correction {{ $statusText }}</p>
<p style="color:#475569;font-size:.9rem;margin:0 0 20px;">
  Your attendance correction request has been <strong>{{ strtolower($statusText) }}</strong>
  by <strong>{{ $handledBy->name }}</strong>.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Date</span>
    <span class="info-value">{{ \Carbon\Carbon::parse($correction->requested_date)->format('d M Y') }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Correction Type</span>
    <span class="info-value">{{ ucfirst(str_replace('_', ' ', $correction->requested_type)) }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Decision</span>
    <span class="info-value"><span class="badge {{ $badgeClass }}">{{ $statusText }}</span></span>
  </div>
  @if($correction->admin_remark)
  <div class="info-row">
    <span class="info-label">Admin Remark</span>
    <span class="info-value" style="max-width:300px;text-align:right;">{{ $correction->admin_remark }}</span>
  </div>
  @endif
  <div class="info-row">
    <span class="info-label">Reviewed By</span>
    <span class="info-value">{{ $handledBy->name }}</span>
  </div>
</div>

@if($approved)
  <p style="font-size:.875rem;color:#059669;background:#ecfdf5;padding:12px 16px;border-radius:8px;border-left:4px solid #10b981;">
    ✅ Your attendance record has been updated accordingly.
  </p>
@else
  <p style="font-size:.875rem;color:#dc2626;background:#fef2f2;padding:12px 16px;border-radius:8px;border-left:4px solid #ef4444;">
    ❌ Your correction request was not approved. Please contact your manager if you have concerns.
  </p>
@endif

<a href="{{ url('/employee/attendance') }}" class="btn {{ $approved ? 'btn-success' : '' }}">View My Attendance</a>
@endsection
