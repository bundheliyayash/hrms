<x-admin-layout>
    <x-slot name="header">
        Edit Role: {{ $role->display_name }}
    </x-slot>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small">Role Slug</label>
                        <input type="text" name="name" class="form-control" value="{{ $role->name }}" {{ $role->name === 'admin' ? 'readonly' : '' }} required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small">Display Name</label>
                        <input type="text" name="display_name" class="form-control" value="{{ $role->display_name }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ $role->description }}</textarea>
                    </div>
                </div>

                <h6 class="fw-bold mb-3 border-bottom pb-2">Modify Permissions</h6>
                
                @foreach($permissions as $category => $items)
                <div class="mb-4">
                    <div class="text-primary small fw-bold text-uppercase mb-2">{{ $category }}</div>
                    <div class="row">
                        @foreach($items as $permission)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="p{{ $permission->id }}" 
                                    {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="p{{ $permission->id }}">
                                    {{ $permission->display_name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <div class="mt-4 pt-3 border-top text-end">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
