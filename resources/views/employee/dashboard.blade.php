<x-admin-layout>
    <div class="d-md-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">My Workspace</h4>
            <p class="text-muted small mb-0">Track your attendance, schedule, and payroll in one place.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                <i class="bi bi-calendar3 me-1"></i> {{ date('l, d F Y') }}
            </span>
        </div>
    </div>

    <style>
        .premium-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; transition: all 0.2s; overflow: hidden; }
        .premium-card:hover { transform: translateY(-3px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.05); }
        .stat-circle { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .action-banner { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 20px; padding: 2rem; color: #fff; }
        .clock-display { font-size: 3rem; font-weight: 800; letter-spacing: -2px; line-height: 1; }
        .table thead th { background: #f8fafc; font-size: 0.7rem; text-transform: uppercase; color: #64748b; padding: 1.25rem 1rem; }
        .table tbody td { padding: 1.25rem 1rem; border-bottom: 1px solid #f1f5f9; }
        .btn-premium { padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.2s; }
        .btn-clock-in { background: #fff; color: #3b82f6; border: none; }
        .btn-clock-in:hover { background: #f1f5f9; transform: scale(1.02); }
        .btn-clock-out { background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(5px); }
        .btn-clock-out:hover { background: rgba(255,255,255,0.25); }
    </style>

    <div class="row g-4 mb-4">
        <!-- Main Stats -->
        <div class="col-lg-8">
            <div class="action-banner shadow-sm mb-4">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h2 class="fw-bold mb-3">Hello, {{ explode(' ', Auth::user()->name)[0] }}!</h2>
                        <p class="opacity-90 mb-4">You are currently assigned to <span class="fw-bold">{{ $assignedSite->site_name ?? 'Head Office' }}</span>. Make sure to clock in when you reach your location.</p>
                        
                        <div class="d-flex gap-3">
                            @if(!$todayAttendance)
                                <form action="{{ route('employee.attendance.clockIn') }}" method="POST" id="clockInForm" class="w-100">
                                    @csrf
                                    <input type="hidden" name="latitude" id="lat">
                                    <input type="hidden" name="longitude" id="lng">
                                    <button type="button" onclick="getLocation()" class="btn btn-premium btn-clock-in w-100">
                                        <i class="bi bi-fingerprint me-2"></i> Clock In Now
                                    </button>
                                </form>
                            @elseif(!$todayAttendance->clock_out)
                                <form action="{{ route('employee.attendance.clockOut') }}" method="POST" class="w-100">
                                    @csrf
                                    <button type="submit" class="btn btn-premium btn-clock-out w-100">
                                        <i class="bi bi-box-arrow-right me-2"></i> End Day
                                    </button>
                                </form>
                            @else
                                <div class="badge bg-white text-success px-4 py-3 rounded-pill fw-bold">
                                    <i class="bi bi-check2-circle me-2"></i> Shift Completed
                                </div>
                            @endif
                        </div>
                        <div id="locationStatus" class="mt-2 small opacity-75"></div>
                    </div>
                    <div class="col-md-5 text-center d-none d-md-block">
                        <div class="clock-display mb-1" id="liveClock">00:00:00</div>
                        <div class="small opacity-75 text-uppercase fw-600 tracking-wider">Live System Time</div>
                    </div>
                </div>
            </div>

            <!-- Attendance Grid -->
            <div class="row g-3">
                @php
                    $statItems = [
                        ['label' => 'Days Present', 'value' => $present, 'icon' => 'calendar-check', 'color' => 'success'],
                        ['label' => 'Late In', 'value' => $late, 'icon' => 'clock-history', 'color' => 'warning'],
                        ['label' => 'Absences', 'value' => $absent, 'icon' => 'calendar-x', 'color' => 'danger'],
                        ['label' => 'Half Days', 'value' => $half_day, 'icon' => 'calendar-plus', 'color' => 'info']
                    ];
                @endphp
                @foreach($statItems as $item)
                <div class="col-6 col-md-3">
                    <div class="premium-card p-3 h-100">
                        <div class="stat-circle bg-{{ $item['color'] }}-subtle text-{{ $item['color'] }} mb-3">
                            <i class="bi bi-{{ $item['icon'] }}"></i>
                        </div>
                        <div class="text-muted small fw-600 text-uppercase mb-1" style="font-size: 0.65rem;">{{ $item['label'] }}</div>
                        <h4 class="fw-bold mb-0">{{ $item['value'] }}</h4>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Sidebar / Recent Activity -->
        <div class="col-lg-4">
            <div class="premium-card h-100">
                <div class="p-3 border-bottom bg-light">
                    <h6 class="fw-bold mb-0">Today's Timeline</h6>
                </div>
                <div class="p-0">
                    <div class="list-group list-group-flush">
                        @if($todayAttendance)
                            <div class="list-group-item border-0 p-3">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="bg-success rounded-circle me-3" style="width: 10px; height: 10px;"></div>
                                    <div class="fw-bold small">Clock In</div>
                                    <div class="ms-auto small text-muted">{{ \Carbon\Carbon::parse($todayAttendance->clock_in)->format('h:i A') }}</div>
                                </div>
                                <div class="ps-4 small text-muted">
                                    @if($todayAttendance->is_verified)
                                        <i class="bi bi-check-circle-fill text-success"></i> Verified Location
                                    @else
                                        <i class="bi bi-exclamation-triangle-fill text-warning"></i> Manual Override
                                    @endif
                                </div>
                            </div>
                            
                            @foreach($todayAttendance->breaks as $break)
                                <div class="list-group-item border-0 p-3 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-warning rounded-circle me-3" style="width: 10px; height: 10px;"></div>
                                        <div class="fw-bold small">Break Profile</div>
                                        <div class="ms-auto small text-muted">{{ \Carbon\Carbon::parse($break->break_start)->format('h:i A') }}</div>
                                    </div>
                                    @if($break->break_end)
                                        <div class="ps-4 small text-muted mt-1">Duration: {{ $break->duration_minutes }} mins</div>
                                    @else
                                        <div class="ps-4 small text-primary mt-1">Currently on break</div>
                                    @endif
                                </div>
                            @endforeach

                            @if($todayAttendance->clock_out)
                                <div class="list-group-item border-0 p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger rounded-circle me-3" style="width: 10px; height: 10px;"></div>
                                        <div class="fw-bold small">Clock Out</div>
                                        <div class="ms-auto small text-muted">{{ \Carbon\Carbon::parse($todayAttendance->clock_out)->format('h:i A') }}</div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-sun fs-1 d-block mb-3 opacity-25"></i>
                                <div class="small fw-600">No activity logged yet today.</div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="p-3 mt-auto">
                    <a href="{{ route('employee.attendance.index') }}" class="btn btn-light w-100 rounded-pill btn-sm fw-600">View Monthly Card</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Chart -->
    <div class="premium-card">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0">Working Hours History</h6>
                <p class="text-muted small mb-0">Your daily hours worked throughout this month.</p>
            </div>
        </div>
        <div class="p-4">
            <div style="height: 250px;">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Live Clock
        function updateClock() {
            const now = new Date();
            document.getElementById('liveClock').innerText = now.toLocaleTimeString([], { hour12: false });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Location Detection
        function getLocation() {
            const status = document.getElementById('locationStatus');
            if (navigator.geolocation) {
                status.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Detecting precise location...';
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        document.getElementById('lat').value = pos.coords.latitude;
                        document.getElementById('lng').value = pos.coords.longitude;
                        document.getElementById('clockInForm').submit();
                    },
                    (err) => { status.innerHTML = '<span class="text-white-50">Error: ' + err.message + '</span>'; }
                );
            }
        }

        // Attendance Chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Hours Worked',
                    data: {!! json_encode($chartData) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] }, ticks: { stepSize: 2 } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</x-admin-layout>
