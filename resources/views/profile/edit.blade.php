<x-admin-layout>
    <x-slot name="header">
        Profile
    </x-slot>

    <div class="row g-4">
        <div class="col-12">
            <div class="alert alert-light border d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">{{ Auth::user()->name }}</h5>
                        <small class="text-muted">{{ Auth::user()->email }}</small>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill">
                        <i class="bi bi-briefcase-fill me-1"></i> 
                        {{ Auth::user()->employeeDetail ? Auth::user()->employeeDetail->designation : 'N/A' }}
                    </span>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info px-3 py-2 rounded-pill ms-2">
                         <i class="bi bi-person-badge me-1"></i> {{ Auth::user()->employeeDetail->employment_type ?? 'On-roll' }}
                    </span>
                    @if(Auth::user()->employeeDetail && Auth::user()->employeeDetail->site)
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-3 py-2 rounded-pill ms-2">
                             <i class="bi bi-geo-alt-fill me-1"></i> {{ Auth::user()->employeeDetail->site->site_name }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card shadow-sm border-0 border-start border-4 border-danger">
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
