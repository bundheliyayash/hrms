<x-admin-layout>
    <x-slot name="header">
        Operations Calendar
    </x-slot>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-muted">Worker Deployment: {{ $start->format('F Y') }}</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.assignments.calendar', ['month' => $start->copy()->subMonth()->month, 'year' => $start->copy()->subMonth()->year]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-chevron-left"></i> Previous
                </a>
                <a href="{{ route('admin.assignments.calendar', ['month' => date('m'), 'year' => date('Y')]) }}" class="btn btn-sm btn-outline-info">Today</a>
                <a href="{{ route('admin.assignments.calendar', ['month' => $start->copy()->addMonth()->month, 'year' => $start->copy()->addMonth()->year]) }}" class="btn btn-sm btn-outline-secondary">
                    Next <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 calendar-table" style="table-layout: fixed;">
                    <thead class="bg-light text-center">
                        <tr>
                            <th style="width: 14.28%">Mon</th>
                            <th style="width: 14.28%">Tue</th>
                            <th style="width: 14.28%">Wed</th>
                            <th style="width: 14.28%">Thu</th>
                            <th style="width: 14.28%">Fri</th>
                            <th style="width: 14.28%">Sat</th>
                            <th style="width: 14.28%">Sun</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $current = $start->copy()->startOfWeek(Carbon\Carbon::MONDAY);
                            $endOfGrid = $end->copy()->endOfWeek(Carbon\Carbon::SUNDAY);
                        @endphp

                        @while($current <= $endOfGrid)
                            @if($current->dayOfWeek == Carbon\Carbon::MONDAY)
                                <tr>
                            @endif

                            <td class="{{ $current->month != $start->month ? 'bg-light opacity-50' : '' }} p-0" style="height: 120px; vertical-align: top;">
                                <div class="p-1 text-end border-bottom bg-light bg-opacity-10 small">
                                    <span class="{{ $current->isToday() ? 'badge bg-primary' : '' }}">{{ $current->day }}</span>
                                </div>
                                <div class="p-1 overflow-auto" style="max-height: 90px;">
                                    @php $dateStr = $current->format('Y-m-d'); @endphp
                                    @if(isset($assignments[$dateStr]))
                                        <div class="d-flex flex-wrap gap-1">
                                            @php 
                                                $siteGroups = $assignments[$dateStr]->groupBy('site.site_name');
                                            @endphp
                                            @foreach($siteGroups as $siteName => $siteAssignments)
                                                <a href="{{ route('admin.assignments.index', ['date' => $dateStr, 'site_id' => $siteAssignments[0]->site_id]) }}" 
                                                   class="badge bg-info text-dark text-truncate d-block text-decoration-none shadow-xs" 
                                                   style="max-width: 100%; border: 1px solid #dee2e6;"
                                                   title="{{ $siteName }}: {{ $siteAssignments->count() }} workers">
                                                   {{ $siteName }} ({{ $siteAssignments->count() }})
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-3 text-muted small opacity-25">
                                            <i class="bi bi-slash-circle"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            @if($current->dayOfWeek == Carbon\Carbon::SUNDAY)
                                </tr>
                            @endif
                            @php $current->addDay(); @endphp
                        @endwhile
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .calendar-table td { position: relative; }
        .calendar-table td:hover { background-color: rgba(248, 249, 250, 0.8) !important; cursor: pointer; }
        .shadow-xs { box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); }
    </style>
</x-admin-layout>
