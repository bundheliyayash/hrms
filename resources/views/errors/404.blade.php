<x-admin-layout>
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
        <div class="text-center">
            <div class="display-1 fw-bold text-primary">404</div>
            <h2 class="h3 mb-4">Page Not Found</h2>
            <p class="text-muted mb-5">
                The page you are looking for might have been removed, had its name changed,<br>
                or is temporarily unavailable.
            </p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary px-4">
                <i class="bi bi-house-door me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
</x-admin-layout>
