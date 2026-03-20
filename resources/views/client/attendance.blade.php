<x-client-portal-layout>
    <div class="d-md-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-geo-alt-fill text-primary me-2"></i>{{ $site->site_name }}
            </h4>
            <p class="text-muted small mb-0">Mark or update attendance for employees at this site</p>
        </div>
        <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill mt-2 mt-md-0">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    @livewire('client-attendance', ['site' => $site, 'date' => request('date', now()->format('Y-m-d'))])
</x-client-portal-layout>
