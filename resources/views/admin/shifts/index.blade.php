<x-admin-layout>
    <x-slot name="header">
        Shift Management
    </x-slot>

    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Shift List</h4>
            <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-lg me-2"></i>Create New Shift
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 border-0">Shift Name</th>
                                <th class="py-3 border-0">Timings</th>
                                <th class="py-3 border-0">Grace Periods</th>
                                <th class="py-3 border-0">Break</th>
                                <th class="py-3 border-0 text-center">Status</th>
                                <th class="px-4 py-3 border-0 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shifts as $shift)
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-bold text-dark">{{ $shift->name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                            {{ \Carbon\Carbon::parse($shift->clock_in_time)->format('h:i A') }} - 
                                            {{ \Carbon\Carbon::parse($shift->clock_out_time)->format('h:i A') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="text-muted">Late:</span> {{ $shift->late_threshold_minutes }}m<br>
                                            <span class="text-muted">Early:</span> {{ $shift->early_out_threshold_minutes }}m
                                        </div>
                                    </td>
                                    <td>
                                        <span class="small">{{ $shift->break_duration_minutes }}m</span>
                                    </td>
                                    <td class="text-center">
                                        @if($shift->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-4 text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.shifts.edit', $shift) }}" class="btn btn-sm btn-light rounded-circle me-1" title="Edit">
                                                <i class="bi bi-pencil-fill text-primary"></i>
                                            </a>
                                            <form action="{{ route('admin.shifts.destroy', $shift) }}" method="POST" class="d-inline" data-confirm-delete="true" data-delete-message="Delete Shift {{ $shift->name }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light rounded-circle" title="Delete">
                                                    <i class="bi bi-trash-fill text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted mb-3">
                                            <i class="bi bi-clock-history display-4"></i>
                                        </div>
                                        <p class="mb-0">No shifts found. Start by creating one!</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($shifts->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $shifts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
