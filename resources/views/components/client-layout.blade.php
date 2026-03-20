@props(['clientName' => 'Client Portal', 'clientSites' => collect()])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $systemSettings['app_name'] ?? config('app.name', 'HRMS') }} - Client Portal</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @livewireStyles

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
            background: linear-gradient(180deg, #0f4c75 0%, #1b262c 100%);
            transition: margin .25s ease-out;
            color: #a3adb9;
            z-index: 1000;
        }
        .sidebar-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .header-logo {
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 1.1rem;
            color: #fff;
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
            color: rgba(255,255,255,0.4);
            font-weight: 700;
        }
        .sidebar-footer {
            margin-top: auto;
            padding: 1.5rem;
            background-color: rgba(0,0,0,0.15);
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            color: #fff;
        }
        .avatar-sm {
            width: 36px;
            height: 36px;
            background-color: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-weight: 600;
            font-size: 0.875rem;
            color: #fff;
        }
        #page-content-wrapper {
            flex: 1;
            min-width: 0;
            background-color: #f4f7fa;
        }
        .top-navbar {
            background-color: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
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
        }
        .nav-icon-btn:hover {
            background-color: #f1f5f9;
            color: #3b82f6;
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
        .client-badge {
            background: linear-gradient(135deg, #0f4c75, #3282b8);
            color: #fff;
            font-size: 0.6rem;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        @media (max-width: 768px) {
            #sidebar-wrapper { margin-left: -260px; position: fixed; height: 100%; }
            body.sb-sidenav-toggled #sidebar-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body>

    <div id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper" class="d-flex flex-column">
            <div class="sidebar-header">
                <div class="d-flex align-items-center">
                    <div class="header-logo">
                        <i class="bi bi-building-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white mb-0" style="font-size: 1.1rem; line-height: 1.2;">
                            {{ $clientName ?? 'Client Portal' }}
                        </div>
                        <div class="text-white-50" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                            <span class="client-badge">CLIENT PORTAL</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="list-group list-group-flush flex-grow-1 overflow-auto">
                <a href="{{ route('client.dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>

                <div class="sidebar-section-title">Your Sites</div>
                @isset($clientSites)
                    @foreach($clientSites as $navSite)
                        <a href="{{ route('client.attendance.form', $navSite->id) }}" 
                           class="list-group-item list-group-item-action {{ request()->is('client/attendance/'.$navSite->id) ? 'active' : '' }}">
                            <i class="bi bi-geo-alt-fill"></i> {{ $navSite->site_name }}
                        </a>
                    @endforeach
                @endisset

                <a href="{{ route('client.history') }}" class="list-group-item list-group-item-action {{ request()->routeIs('client.history') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Attendance History
                </a>
                <a href="{{ route('client.profile') }}" class="list-group-item list-group-item-action {{ request()->routeIs('client.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i> My Profile
                </a>
                <a href="{{ route('client.manual') }}" class="list-group-item list-group-item-action {{ request()->routeIs('client.manual') ? 'active' : '' }}">
                    <i class="bi bi-book"></i> User Manual
                </a>
            </div>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="avatar-sm">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="text-truncate fw-bold small text-white">{{ Auth::user()->name }}</div>
                        <div class="text-truncate small" style="font-size: 0.7rem;">Client Account</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Top Navbar -->
            <nav class="top-navbar navbar navbar-expand-lg navbar-light">
                <div class="container-fluid px-0">
                    <button class="btn nav-icon-btn border-0 shadow-none d-lg-none" id="sidebarToggle">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    
                    <div class="me-auto"></div>

                    <div class="d-flex align-items-center">
                        <div class="dropdown">
                            <div class="user-dropdown dropdown-toggle" data-bs-toggle="dropdown">
                                <span class="avatar-sm bg-primary-subtle text-primary border-0 mb-0 me-2" style="width: 28px; height: 28px; font-size: 0.7rem;">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                                <span class="user-dropdown-name d-none d-sm-block">{{ Auth::user()->name }}</span>
                                <i class="bi bi-chevron-down small text-muted"></i>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
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
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                document.body.classList.toggle('sb-sidenav-toggled');
            });
        }
    </script>
</body>
</html>
