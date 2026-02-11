<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <span>Roles & Permissions</span>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Create New Role
            </a>
        </div>
    </x-slot>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Role Name</th>
                            <th>Display Name</th>
                            <th>Users Count</th>
                            <th>Description</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td class="ps-3"><span class="badge bg-secondary-subtle text-secondary">{{ $role->name }}</span></td>
                            <td class="fw-bold">{{ $role->display_name }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td><small class="text-muted">{{ $role->description }}</small></td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                @if($role->name !== 'admin')
                                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" data-confirm-delete="true" data-delete-message="Delete Role {{ $role->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
