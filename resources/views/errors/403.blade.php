<x-admin-layout>
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
        <div class="text-center">
            <div class="display-1 fw-bold text-danger">403</div>
            <h2 class="h3 mb-4">Access Forbidden</h2>
            <p class="text-muted mb-5">
                Sorry, you do not have permission to access this module.<br>
                Please contact your administrator if you believe this is an error.
            </p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary px-4">
                <i class="bi bi-house-door me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
</x-admin-layout>
