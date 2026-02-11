<x-admin-layout>
    <x-slot name="header">
        Daily Deployment Sheet
    </x-slot>

    <div class="card shadow-sm border-0 mb-4 no-print">
        <div class="card-body bg-light rounded d-flex justify-content-between align-items-center py-3">
            <form action="{{ route('admin.assignments.deployment-sheet') }}" method="GET" class="d-flex gap-2">
                <input type="date" name="date" class="form-control" value="{{ request('date', date('Y-m-d')) }}">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Fetch Sheet</button>
            </form>
            <button onclick="window.print()" class="btn btn-success">
                <i class="bi bi-printer"></i> Print Deployment Sheet
            </button>
        </div>
    </div>

    <div class="deployment-sheet-printable bg-white p-4 shadow mb-5">
        <div class="text-center mb-4 pb-3 border-bottom border-dark">
            <h2 class="mb-0 fw-bold">CLEAN SHEEN HQ</h2>
            <h4 class="text-muted">Daily Workforce Deployment Sheet</h4>
            <div class="mt-2 fw-bold fs-5">Deployment Date: {{ Carbon\Carbon::parse($date)->format('l, d F Y') }}</div>
        </div>

        @forelse($assignments as $siteName => $siteAssignments)
            <div class="mb-5">
                <div class="bg-dark text-white p-2 mb-0 d-flex justify-content-between">
                    <span class="fs-5 fw-bold">SITE: {{ $siteName }}</span>
                    <span class="fs-6">Total Workers: {{ $siteAssignments->count() }}</span>
                </div>
                <table class="table table-bordered border-dark">
                    <thead>
                        <tr class="table-light">
                            <th style="width: 50px">S.No</th>
                            <th>Worker Name</th>
                            <th>Emp ID</th>
                            <th>Shift Schedule</th>
                            <th style="width: 150px">Time In / Sig</th>
                            <th style="width: 150px">Time Out / Sig</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($siteAssignments as $index => $assignment)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $assignment->employee->name }}</td>
                            <td>{{ $assignment->employee->employeeDetail->employee_id ?? 'N/A' }}</td>
                            <td>
                                @if($assignment->shift)
                                    {{ $assignment->shift->name }} ({{ $assignment->shift->start_time->format('H:i') }}-{{ $assignment->shift->end_time->format('H:i') }})
                                @else
                                    Standard Shift (09:00 - 18:00)
                                @endif
                            </td>
                            <td></td>
                            <td></td>
                            <td class="small">{{ $assignment->notes }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="text-center py-5 text-muted h4 border border-dashed rounded">
                No active workers assigned for {{ Carbon\Carbon::parse($date)->format('d/m/Y') }}
            </div>
        @endforelse

        <div class="row mt-5 pt-5 no-print-mt">
            <div class="col-4 text-center">
                <div class="border-top border-dark pt-2 mx-5">Operations Manager</div>
            </div>
            <div class="col-4 text-center">
                <div class="border-top border-dark pt-2 mx-5">Site Supervisor</div>
            </div>
            <div class="col-4 text-center">
                <div class="border-top border-dark pt-2 mx-5">Security / Gate Verify</div>
            </div>
        </div>

        <div class="text-center mt-5 pt-3 small text-muted border-top">
            Print Generated on: {{ now()->format('d/m/Y H:i:s') }} | Clean Sheen HRMS Deployment Module
        </div>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            .card { box-shadow: none !important; border: 0 !important; }
            body { background: white !important; }
            .no-print-mt { margin-top: 100px !important; }
            .deployment-sheet-printable { padding: 0 !important; box-shadow: none !important; }
            .navbar { display: none !important; }
            .sidebar { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; border: 0 !important; }
            .header { display: none !important; }
        }
    </style>
</x-admin-layout>
