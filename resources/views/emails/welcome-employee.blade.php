@extends('emails.layout')

@section('content')
<p class="greeting">Welcome, {{ $employee->name }}! 👋</p>
<p style="color:#475569;font-size:.9rem;margin:0 0 20px;">
  Your employee account has been created on <strong>{{ config('app.name') }}</strong>.
  You can now log in to the Employee Portal to view your attendance, payslips, and apply for leaves.
</p>

<div class="info-box">
  <div class="info-row">
    <span class="info-label">Employee ID</span>
    <span class="info-value">{{ $detail->employee_id }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Designation</span>
    <span class="info-value">{{ $detail->designation }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Department</span>
    <span class="info-value">{{ $detail->department }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Joining Date</span>
    <span class="info-value">{{ \Carbon\Carbon::parse($detail->joining_date)->format('d M Y') }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Login Email</span>
    <span class="info-value">{{ $employee->email }}</span>
  </div>
  <div class="info-row">
    <span class="info-label">Temporary Password</span>
    <span class="info-value" style="font-family:monospace;background:#f1f5f9;padding:2px 8px;border-radius:4px;">{{ $plainPassword }}</span>
  </div>
</div>

<a href="{{ url('/login') }}" class="btn">Login to Employee Portal</a>
<p class="note">⚠️ Please change your password after your first login for security.</p>

<hr class="divider">
<p style="font-size:.85rem;color:#64748b;margin:0;">
  If you have any questions, please contact your manager or the HR department.
</p>
@endsection
