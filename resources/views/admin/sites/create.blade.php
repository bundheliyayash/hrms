<x-admin-layout>
    <x-slot name="header">
        Add New Site
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted">Site Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.sites.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="client_id" class="form-label fw-bold">Client</label>
                        <select name="client_id" id="client_id" class="form-select" required>
                            <option value="">-- Select Client --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="site_name" class="form-label fw-bold">Site Name</label>
                        <input type="text" class="form-control" name="site_name" id="site_name" required>
                        @error('site_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label fw-bold">Address</label>
                        <textarea class="form-control" name="address" id="address" rows="2"></textarea>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="latitude" class="form-label fw-bold">Latitude</label>
                            <button type="button" class="btn btn-sm btn-outline-primary mb-2" onclick="getCurrentLocation()">
                                <i class="bi bi-geo-alt"></i> Get Current Location
                            </button>
                        </div>
                        <input type="number" step="0.00000001" class="form-control" name="latitude" id="latitude" required placeholder="e.g. 40.7128">
                        @error('latitude') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="longitude" class="form-label fw-bold">Longitude</label>
                        <input type="number" step="0.00000001" class="form-control" name="longitude" id="longitude" required placeholder="e.g. -74.0060">
                         @error('longitude') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="radius_meters" class="form-label fw-bold">Radius (Meters)</label>
                        <input type="number" class="form-control" name="radius_meters" id="radius_meters" value="100" required>
                        <div class="form-text">Distance allowed from the center point for valid attendance.</div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                     <i class="bi bi-info-circle"></i> Tip: Use Google Maps to find the Latitude and Longitude of the site location.
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('admin.sites.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Site</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function getCurrentLocation() {
            if ("geolocation" in navigator) {
                const btn = event.currentTarget;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Fetching...';

                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('latitude').value = position.coords.latitude.toFixed(8);
                    document.getElementById('longitude').value = position.coords.longitude.toFixed(8);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, function(error) {
                    alert("Error: " + error.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                });
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }
    </script>
</x-admin-layout>
