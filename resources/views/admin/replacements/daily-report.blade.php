<x-admin-layout>
    <x-slot name="header">
        Staffing Reliability Report
    </x-slot>

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-muted">Substitution Trends: Last 30 Days</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 border-end">
                            <h2 class="fw-bold text-primary">{{ $total }}</h2>
                            <small class="text-muted text-uppercase fw-bold">Total Subs</small>
                        </div>
                        <div class="col-md-3 border-end">
                            <h2 class="fw-bold text-danger">{{ $absentCount }}</h2>
                            <small class="text-muted text-uppercase fw-bold">Absenteeism</small>
                        </div>
                        <div class="col-md-3 border-end">
                            <h2 class="fw-bold text-warning">{{ $emergencyCount }}</h2>
                            <small class="text-muted text-uppercase fw-bold">Emergencies</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="fw-bold text-success">{{ $clientRequestCount }}</h2>
                            <small class="text-muted text-uppercase fw-bold">Client Reqs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-muted">Quick Insights</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Most Impacted Site:</span>
                        <span class="fw-bold">{{ $topImpactedSite ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Top Substitute:</span>
                        <span class="fw-bold text-success">{{ $topSubstitute ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Approval Rate:</span>
                        <span class="fw-bold text-info">{{ $approvalRate }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-muted">Detailed Log: {{ date('F Y') }}</h5>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i> Export PDF</button>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Worker (Out)</th>
                        <th>Worker (In)</th>
                        <th>Impacted Site</th>
                        <th>Reason Category</th>
                        <th>Outcome</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($replacements as $rep)
                    <tr>
                        <td class="ps-3 small">{{ $rep->originalAssignment->assigned_date->format('d/m/y') }}</td>
                        <td class="text-danger fw-bold">{{ $rep->originalWorker->name }}</td>
                        <td class="text-success fw-bold">{{ $rep->replacementWorker->name }}</td>
                        <td>{{ $rep->originalAssignment->site->site_name }}</td>
                        <td>
                            @php 
                                $color = match($rep->reason) {
                                    'absent' => 'bg-danger',
                                    'emergency' => 'bg-warning text-dark',
                                    'client_request' => 'bg-info text-dark',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $color }} rounded-pill">{{ ucfirst($rep->reason) }}</span>
                        </td>
                        <td>
                            <i class="bi bi-{{ $rep->status == 'approved' ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }} me-1"></i>
                            {{ ucfirst($rep->status) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <style>
        @media print {
            .navbar, .sidebar, .btn, .alert { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
    </style>
</x-admin-layout>
