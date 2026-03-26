@extends('emails.layout')

@section('content')
<p class="greeting">New Leave Request — Action Required</p>
<p style="color:#475569;font-size:.9rem;margin:0 0 20px;">
  <strong>{{ $employee->name }}</strong> has submitted a leave request that requires your approval.
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
  @if($leave->is_half_day)
  <div class="info-row">
    <span class="info-label">Half Day</span>
    <span class="info-value">{{ ucfirst($leave->half_day_period ?? '') }} session</span>
  </div>
  @endif
  <div class="info-row">
    <span class="info-label">Reason</span>
    <span class="info-value" style="max-width:300px;text-align:right;">{{ $leave->reason }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Status</span>
    <span class="info-value"><span class="badge badge-warning">Pending</span></span>
  </div>
</div>

<a href="{{ url('/admin/leaves') }}" class="btn">Review Leave Requests</a>
@endsection
