<x-admin-layout>
    <x-slot name="header">
        Edit Site
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-muted">Update Site</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.sites.update', $site->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="client_id" class="form-label fw-bold">Client</label>
                        <select name="client_id" id="client_id" class="form-select" required>
                            <option value="">-- Select Client --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ $site->client_id == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="site_name" class="form-label fw-bold">Site Name</label>
                        <input type="text" class="form-control" name="site_name" id="site_name" value="{{ $site->site_name }}" required>
                        @error('site_name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label fw-bold">Address</label>
                        <textarea class="form-control" name="address" id="address" rows="2">{{ $site->address }}</textarea>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="latitude" class="form-label fw-bold">Latitude</label>
                            <button type="button" class="btn btn-sm btn-outline-primary mb-2" onclick="getCurrentLocation()">
                                <i class="bi bi-geo-alt"></i> Get Current Location
                            </button>
                        </div>
                        <input type="number" step="0.00000001" class="form-control" name="latitude" id="latitude" value="{{ $site->latitude }}" required>
                        @error('latitude') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="longitude" class="form-label fw-bold">Longitude</label>
                        <input type="number" step="0.00000001" class="form-control" name="longitude" id="longitude" value="{{ $site->longitude }}" required>
                         @error('longitude') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="radius_meters" class="form-label fw-bold">Radius (Meters)</label>
                        <input type="number" class="form-control" name="radius_meters" id="radius_meters" value="{{ $site->radius_meters }}" required>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('admin.sites.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Site</button>
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
