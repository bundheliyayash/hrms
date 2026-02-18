<x-admin-layout>
    <x-slot name="header">
        Muster Roll
    </x-slot>

    <div class="mb-3 d-flex justify-content-between">
        <a href="{{ route('admin.reports.index') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
        
        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.reports.muster-roll') }}" class="d-flex gap-2">
            <select name="month" class="form-select form-select-sm">
                @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>
            <select name="year" class="form-select form-select-sm">
                @for($y=date('Y'); $y>=2024; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="window.print()">Print</button>
        </form>
    </div>

    <div class="card shadow-sm" style="overflow-x: auto;">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted">Attendance Sheet - {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-sm text-center small mb-0" style="table-layout: fixed; width: max-content;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 150px; position: sticky; left: 0; background: #fff; z-index: 2;">Employee</th>
                        @for($d=1; $d<=$daysInMonth; $d++)
                            <th style="width: 30px;">{{ $d }}</th>
                        @endfor
                        <th style="width: 50px;">P</th>
                        <th style="width: 50px;">H</th>
                        <th style="width: 50px;">A</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $emp)
                    @php
                        $presentCount = 0;
                        $absentCount = 0;
                        $holidayCount = 0;
                        // Key attendances by day
                        $attendanceMap = [];
                        foreach($emp->attendances as $att) {
                            $day = (int)\Carbon\Carbon::parse($att->date)->day;
                            $attendanceMap[$day] = $att->status; 
                        }
                    @endphp
                    <tr>
                        <td class="fw-bold text-start ps-2" style="position: sticky; left: 0; background: #fff; z-index: 1;">
                            {{ $emp->name }}
                        </td>
                        @for($d=1; $d<=$daysInMonth; $d++)
                            @php
                                $dateStr = Carbon\Carbon::create($year, $month, $d)->format('Y-m-d');
                                $status = $attendanceMap[$d] ?? null;
                                
                                // Check if it's a holiday for this employee
                                $holiday = $holidays->first(function ($h) use ($dateStr, $emp) {
                                    if ($dateStr < $h->start_date->format('Y-m-d') || $dateStr > ($h->end_date ?? $h->start_date)->format('Y-m-d')) return false;
                                    
                                    if ($h->applicable_type === 'all') return true;
                                    if ($h->applicable_type === 'department' && $h->departments->contains('department', $emp->employeeDetail->department)) return true;
                                    if ($h->applicable_type === 'site' && $h->sites->contains('id', $emp->employeeDetail->site_id)) return true;
                                    
                                    return false;
                                });

                                $bg = '';
                                $content = '';
                                $isSunday = Carbon\Carbon::parse($dateStr)->isSunday();
                                
                                if($status == 'present') { $bg = 'bg-success text-white'; $content = 'P'; $presentCount++; }
                                elseif($status == 'late') { $bg = 'bg-warning text-dark'; $content = 'L'; $presentCount++; }
                                elseif($status == 'half_day') { $bg = 'bg-info text-dark'; $content = 'HD'; $presentCount+=0.5; }
                                elseif($status == 'on_leave') { $bg = 'bg-secondary text-white'; $content = 'LV'; }
                                elseif($status == 'holiday' || $holiday || $isSunday) { 
                                    $bg = 'bg-info-subtle text-info fw-bold'; 
                                    $content = 'H'; 
                                    $holidayCount++; 
                                }
                                else { 
                                    // NO LOG FOUND: Treat as Absent
                                    $bg = 'bg-danger-subtle text-danger'; 
                                    $content = 'A'; 
                                    $absentCount++; 
                                }
                            @endphp
                            <td class="{{ $bg }}" title="{{ $status ?? ($holiday ? $holiday->name : ($isSunday ? 'Sunday' : 'Absent')) }}">{{ $content }}</td>
                        @endfor
                        <td class="fw-bold text-success">{{ $presentCount }}</td>
                        <td class="fw-bold text-info">{{ $holidayCount }}</td>
                        <td class="fw-bold text-danger">{{ $absentCount }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
