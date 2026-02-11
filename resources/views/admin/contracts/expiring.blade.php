<x-admin-layout>
    <x-slot name="header">
        Expiring Contracts Alert
    </x-slot>

    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4 py-3">
        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
        <div>
            <strong>Operational Attention Required!</strong> The following contracts are expiring within the next 30 days. Please initiate renewal discussions with clients.
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted">Contracts Nearing Expiry</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Contract #</th>
                            <th>Client Name</th>
                            <th>Expiry Date</th>
                            <th>Days Left</th>
                            <th>Status</th>
                            <th>Auto-Renew</th>
                            <th class="text-end px-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $contract)
                        @php
                            $daysRemaining = now()->diffInDays($contract->end_date, false);
                            $isOverdue = $daysRemaining < 0;
                        @endphp
                        <tr class="{{ $isOverdue ? 'table-danger' : ($daysRemaining <= 7 ? 'table-warning' : '') }}">
                            <td class="px-3 fw-bold">{{ $contract->contract_number }}</td>
                            <td>{{ $contract->client->name }}</td>
                            <td>
                                <span class="fw-bold {{ $isOverdue ? 'text-danger' : '' }}">
                                    {{ $contract->end_date->format('d M, Y') }}
                                </span>
                            </td>
                            <td>
                                @if($isOverdue)
                                    <span class="badge bg-danger">EXPIRED {{ abs($daysRemaining) }} days ago</span>
                                @else
                                    <span class="badge {{ $daysRemaining <= 7 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                        {{ $daysRemaining }} days remaining
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $contract->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($contract->status) }}
                                </span>
                            </td>
                            <td>
                                @if($contract->auto_renew)
                                    <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Yes</span>
                                @else
                                    <span class="text-muted"><i class="bi bi-x-circle me-1"></i> No</span>
                                @endif
                            </td>
                            <td class="text-end px-3">
                                <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-repeat"></i> Renew
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-check2-circle text-success display-4"></i>
                                <p class="mt-3 text-muted">No contracts are expiring soon. Good job!</p>
                            </td>
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
