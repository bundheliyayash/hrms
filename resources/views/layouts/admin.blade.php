<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $systemSettings['app_name'] ?? config('app.name', 'Laravel HRMS') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7fa;
            color: #2c3e50;
        }
        #wrapper {
            display: flex;
            width: 100%;
        }
        #sidebar-wrapper {
            min-height: 100vh;
            width: 260px;
            background-color: #1a1e2b; /* Very dark blue from image */
            transition: margin .25s ease-out;
            color: #a3adb9;
            z-index: 1000;
        }
        #sidebar-wrapper .sidebar-heading {
            padding: 1.5rem 1.5rem;
            color: #fff;
        }
        .sidebar-brand-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0;
            display: flex;
            align-items: center;
        }
        .sidebar-brand-logo {
            width: 32px;
            height: 32px;
            background-color: #3b82f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 1rem;
        }
        .sidebar-subheading {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: -2px;
        }
        #sidebar-wrapper .list-group-item {
            background-color: transparent;
            color: #a3adb9;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            border-radius: 0;
        }
        #sidebar-wrapper .list-group-item i {
            font-size: 1.1rem;
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        #sidebar-wrapper .list-group-item:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.05);
        }
        #sidebar-wrapper .list-group-item.active {
            color: #fff;
            background-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }
        .sidebar-section-title {
            padding: 1.5rem 1.5rem 0.5rem;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
        }
        .sidebar-footer {
            margin-top: auto;
            padding: 1.5rem;
            background-color: rgba(0,0,0,0.1);
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            color: #fff;
        }
        .avatar-sm {
            width: 36px;
            height: 36px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        #page-content-wrapper {
            flex: 1;
            min-width: 0;
            background-color: #f4f7fa;
        }
        .navbar {
            background-color: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
        }
        .search-bar {
            background-color: #f1f5f9;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            width: 350px;
            display: flex;
            align-items: center;
        }
        .search-bar input {
            background: transparent;
            border: none;
            outline: none;
            margin-left: 8px;
            width: 100%;
            font-size: 0.875rem;
        }
        .nav-icon-btn {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #64748b;
            margin-left: 8px;
            position: relative;
        }
        .nav-icon-btn:hover {
            background-color: #f1f5f9;
            color: #3b82f6;
        }
        .badge-notification {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border: 2px solid #fff;
            border-radius: 50%;
        }
        .user-dropdown {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 0.35rem 0.75rem;
            display: flex;
            align-items: center;
            cursor: pointer;
            margin-left: 12px;
        }
        .user-dropdown-name {
            font-size: 0.8125rem;
            font-weight: 600;
            margin-right: 8px;
        }
        .breadcrumb-item + .breadcrumb-item::before {
            content: "/";
        }
        @media (max-width: 768px) {
            #sidebar-wrapper { margin-left: -260px; position: fixed; height: 100%; }
            body.sb-sidenav-toggled #sidebar-wrapper { margin-left: 0; }
        }
        .badge-notification {
            position: absolute;
            top: 2px;
            right: 2px;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            padding: 2px 5px;
            font-size: 0.6rem;
            font-weight: bold;
            line-height: 1;
        }
        .notification-dropdown {
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1) !important;
        }
        .notification-list .dropdown-item:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="{{ (Auth::user() && Auth::user()->role === 'admin') ? 'admin-theme' : '' }}">

    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper" class="d-flex flex-column">
            <div class="sidebar-header">
                <div class="d-flex align-items-center">
                    <div class="header-logo">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white mb-0" style="font-size: 1.1rem; line-height: 1.2;">{{ $systemSettings['company_name'] ?? 'CleanHR' }}</div>
                        <div class="text-white-50" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                            {{ Auth::user()->role === 'employee' ? 'Employee Portal' : 'Admin Portal' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="list-group list-group-flush flex-grow-1 overflow-auto">
                {{-- Shared Dashboard Link --}}
                <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>
                
                {{-- ADMIN/MANAGER/EMPLOYEE SECURED MENU --}}
                
                {{-- Operations Section --}}
                @canany(['manage_assignments', 'view_employees', 'manage_attendance_requests', 'manage_shifts'])
                <div class="sidebar-section-title">Operations</div>
                @if(Auth::user()->hasRole('manager'))
                <a href="{{ route('manager.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Manager Dashboard
                </a>
                @endif
                <a href="{{ route('admin.attendance.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.attendance.*') && !request()->routeIs('*.requests') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check-fill"></i> Daily Attendance
                </a>
                @can('manage_attendance_requests')
                <a href="{{ route('admin.attendance.requests') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.attendance.requests') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle-fill"></i> Correction Requests
                </a>
                @endcan
                @can('manage_shifts')
                <a href="{{ route('admin.shifts.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}">
                    <i class="bi bi-clock-fill"></i> Shifts & Timings
                </a>
                @endcan
                @can('manage_assignments')
                <a href="{{ route('admin.assignments.import') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.assignments.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Bulk Assign Staff
                </a>
                @endcan
                @endcanany

                {{-- Staff & Client Management --}}
                @canany(['manage_employees', 'manage_clients'])
                <div class="sidebar-section-title">Organization</div>
                @can('manage_employees')
                <a href="{{ route('admin.employees.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.employees.index') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Staff Directory
                </a>
                <a href="{{ route('admin.employees.create') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.employees.create') ? 'active' : '' }}">
                    <i class="bi bi-person-plus-fill"></i> Add New Employee
                </a>
                <a href="{{ route('admin.holidays.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event"></i> Holiday Calendar
                </a>
                @endcan
                @can('manage_clients')
                <a href="{{ route('admin.clients.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                    <i class="bi bi-building-fill"></i> Client Partners
                </a>
                <a href="{{ route('admin.sites.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.sites.*') ? 'active' : '' }}">
                    <i class="bi bi-geo-fill"></i> Sites & Geofencing
                </a>
                @endcan
                @endcanany

                {{-- Payroll & Finance --}}
                @canany(['view_payroll', 'manage_payroll', 'view_reports'])
                <div class="sidebar-section-title">Finance & Analytics</div>
                @canany(['view_payroll', 'manage_payroll'])
                <a href="{{ route('admin.payroll.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.payroll.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i> Payroll Register
                </a>
                @endcanany
                @can('view_reports')
                <a href="{{ route('admin.reports.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> All Reports
                </a>
                @endcan
                @endcanany

                {{-- System & Admin --}}
                @canany(['manage_settings', 'manage_rbac', 'view_activity_logs'])
                <div class="sidebar-section-title">Administration</div>
                @can('manage_rbac')
                <a href="{{ route('admin.roles.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock-fill"></i> Roles & Permissions
                </a>
                @endcan
                @can('manage_settings')
                <a href="{{ route('admin.settings.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i> System Settings
                </a>
                @endcan
                @can('view_activity_logs')
                <a href="{{ route('admin.activity-logs.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Audit Trails
                </a>
                @endcan
                @endcanany

                {{-- Employee Self Service --}}
                @if(Auth::user()->hasRole('employee'))
                    <div class="sidebar-section-title">Self Service</div>
                    <a href="{{ route('employee.attendance.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('employee.attendance.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar2-check-fill"></i> My Attendance
                    </a>
                    <a href="{{ route('employee.leaves.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('employee.leaves.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event-fill"></i> My Leaves
                    </a>
                    <a href="{{ route('employee.payroll.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('employee.payroll.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-pdf-fill"></i> My Payslips
                    </a>
                @endif
            </div>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="avatar-sm">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="text-truncate fw-bold small text-white">{{ Auth::user()->name }}</div>
                        <div class="text-truncate small" style="font-size: 0.7rem;">{{ ucfirst(Auth::user()->role) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid px-0">
                    <button class="btn nav-icon-btn border-0 shadow-none d-lg-none" id="sidebarToggle">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    
                    <div class="me-auto"></div>

                    <div class="d-flex align-items-center">

                        <div class="dropdown">
                            <button class="nav-icon-btn border-0 bg-transparent dropdown-toggle shadow-none" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bell fs-5"></i>
                                @if($unreadNotificationsCount > 0)
                                    <span class="badge-notification">{{ $unreadNotificationsCount }}</span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-3 p-0 notification-dropdown" style="width: 320px;">
                                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold">Notifications</h6>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill small">{{ $unreadNotificationsCount }} New</span>
                                </div>
                                <div class="notification-list" style="max-height: 350px; overflow-y: auto;">
                                    @forelse($userNotifications as $notif)
                                        <li>
                                            <a class="dropdown-item p-3 border-bottom notification-item" href="#" onclick="markAsRead(event, {{ $notif->id }})">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="bg-{{ $notif->type }}-subtle text-{{ $notif->type }} rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                            <i class="bi bi-{{ $notif->type === 'success' ? 'check-circle' : ($notif->type === 'danger' ? 'x-circle' : 'info-circle') }}"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-bold small mb-1">{{ $notif->title }}</div>
                                                        <div class="text-muted small mb-1">{{ $notif->message }}</div>
                                                        <div class="text-muted" style="font-size: 0.7rem;">{{ $notif->created_at->diffForHumans() }}</div>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    @empty
                                        <div class="p-4 text-center text-muted">
                                            <i class="bi bi-bell-slash display-6 mb-2"></i>
                                            <p class="small mb-0">No new notifications</p>
                                        </div>
                                    @endforelse
                                </div>
                                <div class="p-2 border-top text-center">
                                    <form action="{{ route('notifications.readAll') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm text-decoration-none small text-muted">Mark All as Read</button>
                                    </form>
                                </div>
                            </ul>
                        </div>

                        <div class="dropdown">
                            <div class="user-dropdown dropdown-toggle" data-bs-toggle="dropdown">
                                <span class="avatar-sm bg-primary-subtle text-primary border-0 mb-0 me-2" style="width: 28px; height: 28px; font-size: 0.7rem;">AK</span>
                                <span class="user-dropdown-name d-none d-sm-block">{{ Auth::user()->name }}</span>
                                <i class="bi bi-chevron-down small text-muted"></i>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
                                <li><a class="dropdown-item py-2 small" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i> Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 small text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid py-4 px-md-4">
                {{ $slot }}
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Sidebar
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                document.body.classList.toggle('sb-sidenav-toggled');
            });
        }

        function markAsRead(e, id) {
            e.preventDefault();
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(() => {
                location.reload(); // Simple reload to refresh count and list
            });
        }
    </script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Global Delete Confirmation
            const deleteForms = document.querySelectorAll('form[data-confirm-delete="true"]');
            
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const title = form.getAttribute('data-delete-title') || 'Are you sure?';
                    const text = form.getAttribute('data-delete-message') || "You won't be able to revert this!";
                    const icon = form.getAttribute('data-delete-icon') || 'warning';
                    const confirmButtonText = form.getAttribute('data-confirm-text') || 'Yes, delete it!';
                    
                    Swal.fire({
                        title: title,
                        text: text,
                        icon: icon,
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: confirmButtonText
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
            
            // Also handle links/buttons with data-confirm-delete (if any)
            // ...
        });
    </script>
</body>
</html>

