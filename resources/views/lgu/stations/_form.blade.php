@php $s = $station ?? null; @endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Station Name</label>
        <input type="text" name="station_name" class="form-control" value="{{ old('station_name', $s->station_name ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Company / Owner</label>
        <input type="text" name="company_owner" class="form-control" value="{{ old('company_owner', $s->company_owner ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Station Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $s->email ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $s->contact_number ?? '') }}">
    </div>
    <div class="col-md-8">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control" value="{{ old('address', $s->address ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="active" @selected(old('status', $s->status ?? 'active')=='active')>Active</option>
            <option value="inactive" @selected(old('status', $s->status ?? '')=='inactive')>Inactive</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Barangay</label>
        <input type="text" name="barangay" id="barangayInput" class="form-control" value="{{ old('barangay', $s->barangay ?? 'Tankulan') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Municipality</label>
        <input type="text" name="municipality" class="form-control" value="{{ old('municipality', $s->municipality ?? 'Manolo Fortich') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Province</label>
        <input type="text" name="province" class="form-control" value="{{ old('province', $s->province ?? 'Bukidnon') }}" required>
    </div>
</div>

<hr class="my-4">
<label class="form-label mb-1">Station Location</label>
<p class="text-muted-gg small">Click on the map or drag the marker to the exact station location. Latitude/longitude are saved automatically.</p>

<div class="d-flex gap-2 mb-2">
    <input type="text" id="locationSearch" class="form-control" placeholder="Search a place to jump the map there...">
    <button type="button" id="useCurrentLocation" class="btn btn-outline-brand text-nowrap"><i class="bi bi-crosshair"></i> Use My Location</button>
</div>
<div id="locationPicker" style="height:360px; border-radius:12px; overflow:hidden; border:1px solid var(--gg-border);"></div>

<div class="row g-3 mt-1">
    <div class="col-md-6">
        <label class="form-label">Latitude</label>
        <input type="number" step="any" name="latitude" id="latitudeInput" class="form-control" value="{{ old('latitude', $s->latitude ?? 8.3696) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Longitude</label>
        <input type="number" step="any" name="longitude" id="longitudeInput" class="form-control" value="{{ old('longitude', $s->longitude ?? 124.8642) }}" required>
    </div>
</div>

@push('scripts')
<script>
    const latInput = document.getElementById('latitudeInput');
    const lngInput = document.getElementById('longitudeInput');
    const startLat = parseFloat(latInput.value) || 8.3696; // default: Manolo Fortich, Bukidnon
    const startLng = parseFloat(lngInput.value) || 124.8642;

    const pickerMap = L.map('locationPicker').setView([startLat, startLng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(pickerMap);

    const pinIcon = L.divIcon({
        className: '', html: '<div class="gg-marker-pin selected"><i class="bi bi-fuel-pump-fill"></i></div>',
        iconSize: [28, 28], iconAnchor: [14, 28],
    });

    const marker = L.marker([startLat, startLng], { draggable: true, icon: pinIcon }).addTo(pickerMap);

    function updateInputs(latlng) {
        latInput.value = latlng.lat.toFixed(7);
        lngInput.value = latlng.lng.toFixed(7);
    }

    marker.on('dragend', () => updateInputs(marker.getLatLng()));
    pickerMap.on('click', (e) => { marker.setLatLng(e.latlng); updateInputs(e.latlng); });

    document.getElementById('useCurrentLocation').addEventListener('click', function () {
        if (!navigator.geolocation) { alert('Geolocation is not supported by your browser.'); return; }
        navigator.geolocation.getCurrentPosition(function (pos) {
            const latlng = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            pickerMap.setView(latlng, 16);
            marker.setLatLng(latlng);
            updateInputs(latlng);
        }, function () {
            alert('Unable to get your current location.');
        });
    });

    document.getElementById('locationSearch').addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const query = this.value + ', Manolo Fortich, Bukidnon, Philippines';
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
            .then(r => r.json())
            .then(results => {
                if (!results.length) { alert('Location not found.'); return; }
                const { lat, lon } = results[0];
                const latlng = { lat: parseFloat(lat), lng: parseFloat(lon) };
                pickerMap.setView(latlng, 16);
                marker.setLatLng(latlng);
                updateInputs(latlng);
            });
    });
</script>
@endpush
