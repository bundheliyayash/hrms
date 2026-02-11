<x-admin-layout>
    <x-slot name="header">
        Client Management
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-muted">All Clients</h5>
            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-briefcase"></i> Add Client
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Client Name</th>
                            <th>Contact</th>
                            <th>Service Period</th>
                            <th>Status</th>
                            <th class="text-center">Sites</th>
                            <th class="text-end px-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        <tr>
                            <td class="px-3 fw-bold">{{ $client->name }}</td>
                            <td>
                                <div><i class="bi bi-envelope small text-muted"></i> {{ $client->email ?? '-' }}</div>
                                <div><i class="bi bi-telephone small text-muted"></i> {{ $client->phone ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="small fw-bold">{{ $client->service_start_date ? \Carbon\Carbon::parse($client->service_start_date)->format('d M, Y') : 'N/A' }}</div>
                                <div class="text-muted extra-small">Ends: {{ $client->service_end_date ? \Carbon\Carbon::parse($client->service_end_date)->format('d M, Y') : 'Open Ended' }}</div>
                            </td>
                            <td>
                                @if($client->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">In-active</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $client->sites->count() }}</span>
                            </td>
                            <td class="text-end px-3">
                                <a href="{{ route('admin.clients.edit', $client->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.clients.destroy', $client->id) }}" method="POST" class="d-inline" data-confirm-delete="true" data-delete-message="Archive {{ $client->name }}? All associated sites will be hidden.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $clients->links() }}
        </div>
    </div>
</x-admin-layout>
