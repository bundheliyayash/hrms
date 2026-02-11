<x-admin-layout>
    <x-slot name="header">
        Contract Management
    </x-slot>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Contracts</h6>
                    <h3 class="mb-0">{{ \App\Models\Contract::count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-success border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Active Contracts</h6>
                    <h3 class="mb-0">{{ \App\Models\Contract::where('status', 'active')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-info border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Permanent</h6>
                    <h3 class="mb-0">{{ \App\Models\Contract::where('contract_type', 'permanent')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-warning border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Expiring Soon</h6>
                    <h3 class="mb-0">{{ \App\Models\Contract::expiring(30)->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-muted">Contract Registry</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.contracts.expiring') }}" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-calendar-range"></i> Expiring Alerts
                </a>
                <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-file-earmark-plus"></i> New Contract
                </a>
            </div>
        </div>
        
        <div class="card-body border-bottom">
            <form action="{{ route('admin.contracts.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search Contract # or Client..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="client_id" class="form-select">
                        <option value="">All Clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="permanent" {{ request('type') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                        <option value="temporary" {{ request('type') == 'temporary' ? 'selected' : '' }}>Temporary</option>
                        <option value="one_day" {{ request('type') == 'one_day' ? 'selected' : '' }}>One-Day</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-info text-white w-100"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Contract #</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Billing Rule</th>
                            <th>Period</th>
                            <th>Workers (Req/Act)</th>
                            <th>Status</th>
                            <th class="text-end px-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $contract)
                        <tr>
                            <td class="px-3 fw-bold">{{ $contract->contract_number }}</td>
                            <td>{{ $contract->client->name }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($contract->contract_type) }}</span>
                            </td>
                            <td>
                                <div>{{ ucfirst(str_replace('_', ' ', $contract->billing_type)) }}</div>
                                <small class="text-success fw-bold">₹{{ number_format($contract->rate_per_day, 2) }}</small>
                            </td>
                            <td>
                                <div class="small">{{ $contract->start_date->format('d M Y') }}</div>
                                @if($contract->end_date)
                                    <div class="small text-muted">to {{ $contract->end_date->format('d M Y') }}</div>
                                @else
                                    <span class="badge bg-light text-dark small">Continuous</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $contract->minimum_workers_required }}</span> / 
                                <span class="badge bg-dark">{{ $contract->actual_workers_assigned }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $contract->status == 'active' ? 'bg-success' : ($contract->status == 'draft' ? 'bg-primary' : 'bg-secondary') }}">
                                    {{ ucfirst($contract->status) }}
                                </span>
                            </td>
                            <td class="text-end px-3">
                                <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <div class="dropdown d-inline">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($contract->status !== 'active')
                                        <li>
                                            <form action="{{ route('admin.contracts.toggle-status', $contract) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="dropdown-item text-success"><i class="bi bi-play-circle me-2"></i> Activate</button>
                                            </form>
                                        </li>
                                        @endif
                                        @if($contract->status === 'active')
                                        <li>
                                            <form action="{{ route('admin.contracts.toggle-status', $contract) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="dropdown-item text-muted"><i class="bi bi-check-circle me-2"></i> Mark Completed</button>
                                            </form>
                                        </li>
                                        @endif
                                        @if(!in_array($contract->status, ['completed', 'cancelled']))
                                        <li>
                                            <form action="{{ route('admin.contracts.toggle-status', $contract) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-x-circle me-2"></i> Cancel</button>
                                            </form>
                                        </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.contracts.destroy', $contract->id) }}" method="POST" data-confirm-delete="true" data-delete-message="Delete this contract?">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No contracts found matching your filters.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $contracts->links() }}
        </div>
    </div>
</x-admin-layout>
