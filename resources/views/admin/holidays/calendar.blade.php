<x-admin-layout>
    <x-slot name="header">
        Holiday Calendar
    </x-slot>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <form action="{{ route('admin.holidays.calendar') }}" method="GET" class="d-flex gap-2 align-items-center">
                        <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                            @for($m=1; $m<=12; $m++)
                                <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                        <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                            @for($y=date('Y')-1; $y<=date('Y')+1; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </form>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.holidays.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-list-ul me-1"></i> List View
                        </a>
                        <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Add Holiday
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
        $daysInMonth = $startOfMonth->daysInMonth;
        $firstDayOfWeek = $startOfMonth->dayOfWeek; // 0 (Sun) to 6 (Sat)
        $calendarDays = [];
        
        // Padding for the first week
        for($i=0; $i<$firstDayOfWeek; $i++) {
            $calendarDays[] = null;
        }
        
        // Days of the month
        for($d=1; $d<=$daysInMonth; $d++) {
            $calendarDays[] = $d;
        }
    @endphp

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="calendar-grid">
                <div class="calendar-header">Sun</div>
                <div class="calendar-header">Mon</div>
                <div class="calendar-header">Tue</div>
                <div class="calendar-header">Wed</div>
                <div class="calendar-header">Thu</div>
                <div class="calendar-header">Fri</div>
                <div class="calendar-header">Sat</div>

                @foreach($calendarDays as $day)
                    @if($day)
                        @php
                            $currDateStr = \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d');
                            $dayHolidays = $holidays->filter(function($h) use ($currDateStr) {
                                return $currDateStr >= $h->start_date->format('Y-m-d') && 
                                       $currDateStr <= ($h->end_date ?? $h->start_date)->format('Y-m-d');
                            });
                        @endphp
                        <div class="calendar-day {{ $dayHolidays->count() > 0 ? 'has-holiday' : '' }}">
                            <span class="day-number">{{ $day }}</span>
                            <div class="holiday-indicators mt-1">
                                @foreach($dayHolidays as $holiday)
                                    <div class="holiday-item {{ $holiday->type }}" title="{{ $holiday->name }}">
                                        {{ $holiday->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="calendar-day padding"></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <style>
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            border-top: 1px solid #eee;
            border-left: 1px solid #eee;
        }
        .calendar-header {
            background: #f8f9fa;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #666;
            border-right: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        .calendar-day {
            min-height: 120px;
            padding: 10px;
            border-right: 1px solid #eee;
            border-bottom: 1px solid #eee;
            position: relative;
        }
        .calendar-day.padding {
            background: #fafafa;
        }
        .day-number {
            font-weight: 600;
            color: #444;
            font-size: 0.9rem;
        }
        .holiday-item {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 4px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 500;
        }
        .holiday-item.paid {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .holiday-item.unpaid {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .holiday-item.optional {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
        }
        .calendar-day.has-holiday {
            background: #fff;
        }
        @media (max-width: 768px) {
            .calendar-day {
                min-height: 80px;
                padding: 5px;
            }
            .holiday-item {
                font-size: 0.6rem;
                padding: 1px 4px;
            }
        }
    </style>
</x-admin-layout>
