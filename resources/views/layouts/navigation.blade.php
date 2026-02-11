<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <x-application-logo style="height: 36px; width: auto;" />
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Navigation Links -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        {{ __('Dashboard') }}
                    </a>
                </li>

                @if(Auth::user()->role === 'admin')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}">
                            {{ __('Employees') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}" href="{{ route('admin.leaves.index') }}">
                            {{ __('Leaves') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.payroll.*') ? 'active' : '' }}" href="{{ route('admin.payroll.index') }}">
                            {{ __('Payroll') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                            {{ __('Reports') }}
                        </a>
                    </li>
                @elseif(Auth::user()->role === 'employee')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('employee.attendance.*') ? 'active' : '' }}" href="{{ route('employee.attendance.index') }}">
                            {{ __('Attendance') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('employee.leaves.*') ? 'active' : '' }}" href="{{ route('employee.leaves.index') }}">
                            {{ __('My Leaves') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('employee.payroll.*') ? 'active' : '' }}" href="{{ route('employee.payroll.index') }}">
                            {{ __('My Payslips') }}
                        </a>
                    </li>
                @endif
            </ul>

            <!-- User Dropdown -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                {{ __('Profile') }}
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
