<x-admin-layout>
    <x-slot name="header">
        Add New Site
    </x-slot>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #map { z-index: 1; margin-top: 10px; border-radius: 8px; border: 1px solid #dee2e6; }
    </style>

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
                        <textarea class="form-control" name="address" id="address" rows="2" placeholder="Descriptive address..."></textarea>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="latitude" class="form-label fw-bold">Latitude</label>
                            <button type="button" class="btn btn-sm btn-outline-primary mb-2" onclick="getCurrentLocation()">
                                <i class="bi bi-geo-alt"></i> Get Current Location
                            </button>
                        </div>
                        <input type="number" step="0.00000001" class="form-control" name="latitude" id="latitude" required placeholder="e.g. 21.1702">
                        @error('latitude') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="longitude" class="form-label fw-bold">Longitude</label>
                        <input type="number" step="0.00000001" class="form-control" name="longitude" id="longitude" required placeholder="e.g. 72.8311">
                         @error('longitude') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="radius_meters" class="form-label fw-bold">Radius (Meters)</label>
                        <input type="number" class="form-control" name="radius_meters" id="radius_meters" value="100" required>
                        <div class="form-text">Distance allowed from the center point for valid attendance.</div>
                    </div>

                    <div class="col-12 mt-4">
                        <label class="form-label fw-bold">Map Location</label>
                        <div id="map" style="height: 400px; width: 100%;"></div>
                        <div class="form-text text-muted mt-2">
                             Click on the map to set location or drag the marker. The blue circle represents the attendance radius.
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <a href="{{ route('admin.sites.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Create Site</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        let map, marker, circle;
        const defaultLat = 21.1702; // Center of India
        const defaultLng = 72.8311;

        document.addEventListener('DOMContentLoaded', function() {
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const radiusInput = document.getElementById('radius_meters');

            // Initialize Map
            let initialLat = parseFloat(latInput.value) || defaultLat;
            let initialLng = parseFloat(lngInput.value) || defaultLng;
            let initialRadius = parseInt(radiusInput.value) || 100;

            map = L.map('map').setView([initialLat, initialLng], 5);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

            circle = L.circle([initialLat, initialLng], {
                radius: initialRadius,
                color: '#3b82f6',
                fillColor: '#3b82f6',
                fillOpacity: 0.2,
                weight: 1
            }).addTo(map);

            // Click Map
            map.on('click', function(e) {
                updatePosition(e.latlng.lat, e.latlng.lng);
            });

            // Drag Marker
            marker.on('dragend', function() {
                const pos = marker.getLatLng();
                updatePosition(pos.lat, pos.lng);
            });

            // Input fields listeners
            latInput.addEventListener('input', syncFromFields);
            lngInput.addEventListener('input', syncFromFields);
            radiusInput.addEventListener('input', () => {
                const r = parseInt(radiusInput.value) || 0;
                circle.setRadius(r);
            });

            function syncFromFields() {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    const pos = [lat, lng];
                    marker.setLatLng(pos);
                    circle.setLatLng(pos);
                    map.panTo(pos);
                }
            }

            function updatePosition(lat, lng) {
                const pos = [lat, lng];
                marker.setLatLng(pos);
                circle.setLatLng(pos);
                latInput.value = lat.toFixed(8);
                lngInput.value = lng.toFixed(8);
            }
        });

        function getCurrentLocation() {
            if ("geolocation" in navigator) {
                const btn = event.currentTarget;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Locating...';

                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    document.getElementById('latitude').value = lat.toFixed(8);
                    document.getElementById('longitude').value = lng.toFixed(8);
                    
                    if (marker && map && circle) {
                        const pos = [lat, lng];
                        marker.setLatLng(pos);
                        circle.setLatLng(pos);
                        map.setView(pos, 16);
                    }

                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, function(error) {
                    alert("Geolocation Error: " + error.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, { enableHighAccuracy: true });
            } else {
                alert("Geolocation not supported.");
            }
        }
    </script>
</x-admin-layout>
