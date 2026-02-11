<x-admin-layout>
    <x-slot name="header">
        Site Management
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-muted">All Sites</h5>
            <a href="{{ route('admin.sites.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-geo-alt"></i> Add Site
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Site Name</th>
                            <th>Client</th>
                            <th>Coordinates (Lat, Long)</th>
                            <th>Status</th>
                            <th>Radius</th>
                            <th class="text-end px-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sites as $site)
                        <tr>
                            <td class="px-3 fw-bold">
                                <div>{{ $site->site_name }}</div>
                                <div class="small text-muted fw-normal">{{ \Illuminate\Support\Str::limit($site->address, 30) }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ optional($site->client)->name ?? 'Archived Client' }}</span>
                            </td>
                             <td class="font-monospace small">
                                <div>{{ $site->latitude }},</div>
                                <div>{{ $site->longitude }}</div>
                            </td>
                            <td>
                                @php 
                                    $clientActive = optional($site->client)->isServiceActive() ?? false;
                                    $siteActive = $site->is_active && $clientActive;
                                @endphp
                                @if($siteActive)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Active</span>
                                @elseif(!$site->is_active)
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">In-active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Service Expired</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $site->radius_meters }} m</span>
                            </td>
                            <td class="text-end px-3">
                                <form action="{{ route('admin.sites.toggle-status', $site->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $site->is_active ? 'warning' : 'success' }}" title="{{ $site->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $site->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.sites.edit', $site->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.sites.destroy', $site->id) }}" method="POST" class="d-inline" data-confirm-delete="true" data-delete-message="Archive {{ $site->site_name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Archive">
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
            {{ $sites->links() }}
        </div>
    </div>
</x-admin-layout>
