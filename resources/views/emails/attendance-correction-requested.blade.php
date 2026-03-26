@extends('emails.layout')

@section('content')
<p class="greeting">Attendance Correction Request — Review Needed</p>
<p style="color:#475569;font-size:.9rem;margin:0 0 20px;">
  <strong>{{ $employee->name }}</strong> has submitted an attendance correction request.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Employee</span>
    <span class="info-value">{{ $employee->name }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Employee ID</span>
    <span class="info-value">{{ $employee->employeeDetail->employee_id ?? 'N/A' }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Date</span>
    <span class="info-value">{{ \Carbon\Carbon::parse($correction->requested_date)->format('d M Y') }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Correction Type</span>
    <span class="info-value">
      <span class="badge badge-warning">{{ ucfirst(str_replace('_', ' ', $correction->requested_type)) }}</span>
    </span>
  </div>
  @if($correction->requested_time)
  <div class="info-row">
    <span class="info-label">Requested Time</span>
    <span class="info-value" style="font-family:monospace;">{{ $correction->requested_time }}</span>
  </div>
  @endif
  <div class="info-row">
    <span class="info-label">Reason</span>
    <span class="info-value" style="max-width:300px;text-align:right;">{{ $correction->reason }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Status</span>
    <span class="info-value"><span class="badge badge-warning">Pending Review</span></span>
  </div>
</div>

<a href="{{ url('/admin/attendance/requests') }}" class="btn">Review Correction Requests</a>
@endsection
