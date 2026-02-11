<x-admin-layout>
    <x-slot name="header">
        Secure Documents Manager
    </x-slot>

    <div class="row">
        <!-- Upload Form -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-muted"><i class="bi bi-upload me-2"></i> Upload Document</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Document Title</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Employee Handbook">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File</label>
                            <input type="file" name="document" class="form-control" required>
                            <div class="form-text">PDF, DOC, XLS, Images (Max 5MB)</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-cloud-upload me-2"></i> Secure Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Documents List -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-muted"><i class="bi bi-files me-2"></i> Stored Documents</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">Title</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $doc)
                                <tr>
                                    <td class="px-4 fw-bold">{{ $doc->title }}</td>
                                    <td>
                                        <small>{{ $doc->uploader->name }}</small>
                                    </td>
                                    <td class="small text-muted">{{ $doc->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.documents.download', $doc->id) }}" class="btn btn-outline-primary" title="Secure Download">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <form action="{{ route('admin.documents.destroy', $doc->id) }}" method="POST" data-confirm-delete="true" data-delete-message="Delete {{ $doc->title }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">No documents found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    {{ $documents->links() }}
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
