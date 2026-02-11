<x-admin-layout>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">System / Technical Report</h4>
            <p class="text-muted small mb-0">Database schema information for administrators</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Reports
        </a>
    </div>

    <!-- Summary Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                            <i class="bi bi-database fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Tables</div>
                            <div class="fs-4 fw-bold">{{ $tableCount }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold">Database Tables</h6>
        </div>
        <div class="card-body p-0">
            <div class="accordion" id="tablesAccordion">
                @foreach($schemaInfo as $index => $table)
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed py-3" type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#table{{ $index }}" 
                                aria-expanded="false">
                            <div class="d-flex align-items-center w-100">
                                <i class="bi bi-table text-primary me-2"></i>
                                <span class="fw-semibold">{{ $table['table'] }}</span>
                                <span class="badge bg-secondary ms-2">{{ $table['column_count'] }} columns</span>
                            </div>
                        </button>
                    </h2>
                    <div id="table{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#tablesAccordion">
                        <div class="accordion-body bg-light">
                            <table class="table table-sm table-bordered mb-0 bg-white">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small">#</th>
                                        <th class="small">Column Name</th>
                                        <th class="small">Data Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($table['columns'] as $colIndex => $column)
                                    <tr>
                                        <td class="small text-muted">{{ $colIndex + 1 }}</td>
                                        <td class="small fw-medium">{{ $column['name'] }}</td>
                                        <td class="small"><code>{{ $column['type'] }}</code></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-admin-layout>
