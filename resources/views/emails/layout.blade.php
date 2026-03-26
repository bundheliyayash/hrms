<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $subject ?? config('app.name') }}</title>
<style>
  body { margin:0; padding:0; background:#f4f7fa; font-family:'Segoe UI',Arial,sans-serif; color:#2c3e50; }
  .wrapper { max-width:600px; margin:32px auto; background:#fff; border-radius:12px;
             box-shadow:0 4px 20px rgba(0,0,0,.08); overflow:hidden; }
  .header { background:linear-gradient(135deg,#1a1e2b 0%,#2d3a52 100%);
            padding:28px 32px; display:flex; align-items:center; gap:14px; }
  .header-logo { width:40px; height:40px; background:#3b82f6; border-radius:10px;
                 display:inline-flex; align-items:center; justify-content:center;
                 font-size:1.1rem; color:#fff; font-weight:700; }
  .header-title { color:#fff; font-size:1.1rem; font-weight:700; margin:0; }
  .header-sub { color:rgba(255,255,255,.55); font-size:.75rem; margin:2px 0 0; }
  .body { padding:32px; }
  .greeting { font-size:1rem; font-weight:600; margin:0 0 6px; }
  .divider { border:none; border-top:1px solid #e2e8f0; margin:24px 0; }
  .info-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;
              padding:18px 20px; margin:20px 0; }
  .info-row { display:flex; justify-content:space-between; align-items:center;
              padding:6px 0; font-size:.875rem; border-bottom:1px solid #f1f5f9; }
  .info-row:last-child { border-bottom:none; }
  .info-label { color:#64748b; font-weight:500; }
  .info-value { font-weight:600; color:#1e293b; text-align:right; }
  .badge { display:inline-block; padding:.25rem .75rem; border-radius:999px;
           font-size:.75rem; font-weight:700; }
  .badge-success { background:#ecfdf5; color:#059669; }
  .badge-danger  { background:#fef2f2; color:#dc2626; }
  .badge-warning { background:#fff7ed; color:#d97706; }
  .badge-info    { background:#eff6ff; color:#2563eb; }
  .btn { display:inline-block; padding:11px 28px; background:#3b82f6; color:#fff !important;
         border-radius:8px; text-decoration:none; font-weight:600; font-size:.875rem;
         margin:20px 0 8px; }
  .btn-success { background:#10b981; }
  .note { font-size:.8rem; color:#94a3b8; margin-top:8px; }
  .footer { background:#f8fafc; padding:20px 32px; border-top:1px solid #e2e8f0;
            text-align:center; font-size:.75rem; color:#94a3b8; }
  .footer strong { color:#64748b; }
</style>
</head>
<body>
<div class="wrapper">
  <!-- Header -->
  <div class="header">
    <div class="header-logo">C</div>
    <div>
      <div class="header-title">{{ config('app.name', 'CleanHR HRMS') }}</div>
      <div class="header-sub">Human Resource Management System</div>
    </div>
  </div>

  <!-- Body -->
  <div class="body">
    @yield('content')
  </div>

  <!-- Footer -->
  <div class="footer">
    <p style="margin:0 0 4px;">
      <strong>{{ config('app.name', 'CleanHR HRMS') }}</strong> &mdash; This is an automated email, please do not reply.
    </p>
    <p style="margin:0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
  </div>
</div>
</body>
</html>
