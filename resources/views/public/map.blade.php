@extends('layouts.app')
@section('title', 'Station Map')

@section('content')
<h4 class="mb-1">Interactive Station Map</h4>
<p class="text-muted-gg mb-3">Locate participating gasoline stations across Manolo Fortich, Bukidnon.</p>

<div class="gg-map-shell">
    <div class="gg-map-controls d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div class="input-group" style="max-width:340px;">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted-gg"></i></span>
            <input type="text" id="mapSearch" class="form-control border-start-0" placeholder="Search stations on the map">
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <select id="availabilityFilter" class="form-select form-select-sm" style="width:auto;">
                <option value="">Any Availability</option>
                <option value="enough">🟢 Has Enough Gasoline</option>
                <option value="almost_empty">🟡 Almost Empty</option>
                <option value="no_fuel">🔴 No Gasoline</option>
            </select>
            <button id="nearMeBtn" type="button" class="btn btn-sm btn-outline-brand"><i class="bi bi-crosshair"></i> Near Me</button>
        </div>
    </div>
    <div class="gg-map-layout">
        <div id="mainMap" class="gg-map-canvas"></div>
        <div class="gg-map-list" id="mapStationList"></div>
    </div>
</div>

@push('scripts')
<script>
    const allStations = @json($stations);
    const map = L.map('mainMap').setView([8.3696, 124.8642], 13); // centered on Manolo Fortich, Bukidnon
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);

    const availabilityMeta = {
        enough: { label: 'Has Enough Gasoline', color: '#15803D' },
        almost_empty: { label: 'Almost Empty', color: '#B45309' },
        no_fuel: { label: 'No Gasoline', color: '#B91C1C' },
        unknown: { label: 'No Data Yet', color: '#6B7280' },
    };

    function gasStationIcon(selected = false) {
        return L.divIcon({
            className: '',
            html: `<div class="gg-marker-pin${selected ? ' selected' : ''}"><i class="bi bi-fuel-pump-fill"></i></div>`,
            iconSize: [28, 28], iconAnchor: [14, 28], popupAnchor: [0, -26],
        });
    }

    const markers = {};
    let activeMarkerId = null;

    function setActiveMarker(id) {
        if (activeMarkerId !== null && markers[activeMarkerId]) markers[activeMarkerId].setIcon(gasStationIcon(false));
        markers[id].setIcon(gasStationIcon(true));
        activeMarkerId = id;
    }

    function popupHtml(s) {
        const meta = availabilityMeta[s.availability] || availabilityMeta.unknown;
        const priceLines = (s.current_prices_list || []).map(p =>
            `<div class="price-line">${p.fuel_type.name}: <strong>₱${Number(p.price).toFixed(2)}</strong></div>`
        ).join('');
        return `<div class="gg-popup">
                    <h6>${s.station_name}</h6>
                    <div class="addr">${s.address}, ${s.barangay}</div>
                    <div class="price-line" style="color:${meta.color}; font-weight:700;">● ${meta.label}</div>
                    ${priceLines || '<div class="text-muted-gg" style="font-size:.8rem;">No prices reported yet</div>'}
                    <a href="/stations/${s.id}">View Details →</a>
                </div>`;
    }

    const listEl = document.getElementById('mapStationList');

    function renderList(filtered) {
        listEl.innerHTML = '';
        filtered.forEach(s => {
            const meta = availabilityMeta[s.availability] || availabilityMeta.unknown;
            const first = (s.current_prices_list || [])[0];
            const item = document.createElement('div');
            item.className = 'gg-map-list-item';
            item.innerHTML = `<div class="name">${s.station_name}</div>
                               <div class="addr"><i class="bi bi-geo-alt-fill"></i> ${s.address}</div>
                               <div class="price" style="color:${meta.color};">● ${meta.label}</div>
                               ${first ? `<div class="price">₱${Number(first.price).toFixed(2)} / L ${first.fuel_type.name}</div>` : ''}`;
            item.addEventListener('click', () => {
                map.setView([s.latitude, s.longitude], 17);
                setActiveMarker(s.id);
                markers[s.id].openPopup();
            });
            listEl.appendChild(item);
        });
    }

    allStations.forEach(s => {
        markers[s.id] = L.marker([s.latitude, s.longitude], { icon: gasStationIcon(false) }).addTo(map).bindPopup(popupHtml(s));
        markers[s.id].on('click', () => setActiveMarker(s.id));
    });
    renderList(allStations);

    function applyFilters() {
        const term = document.getElementById('mapSearch').value.toLowerCase();
        const availability = document.getElementById('availabilityFilter').value;

        const filtered = allStations.filter(s => {
            const matchesTerm = s.station_name.toLowerCase().includes(term) || s.address.toLowerCase().includes(term);
            const matchesAvailability = !availability || s.availability === availability;
            return matchesTerm && matchesAvailability;
        });

        renderList(filtered);
        Object.values(markers).forEach(m => map.removeLayer(m));
        filtered.forEach(s => markers[s.id].addTo(map));
    }

    document.getElementById('mapSearch').addEventListener('input', applyFilters);
    document.getElementById('availabilityFilter').addEventListener('change', applyFilters);

    // Nearest-station finder: browser geolocation -> backend haversine ranking
    document.getElementById('nearMeBtn').addEventListener('click', function () {
        if (!navigator.geolocation) { alert('Geolocation is not supported by your browser.'); return; }
        navigator.geolocation.getCurrentPosition(function (pos) {
            const { latitude, longitude } = pos.coords;
            map.setView([latitude, longitude], 14);
            L.marker([latitude, longitude]).addTo(map).bindPopup('You are here').openPopup();

            fetch(`{{ route('nearest') }}?lat=${latitude}&lng=${longitude}`)
                .then(r => r.json())
                .then(nearest => {
                    listEl.innerHTML = '<div class="gg-map-list-item" style="background:var(--gg-accent-light);"><strong>Nearest Stations</strong></div>';
                    nearest.forEach(s => {
                        const meta = availabilityMeta[s.availability] || availabilityMeta.unknown;
                        const first = (s.prices || [])[0];
                        const item = document.createElement('div');
                        item.className = 'gg-map-list-item';
                        item.innerHTML = `<div class="name">${s.station_name} <span class="text-muted-gg" style="font-weight:400;">(${s.distance_km} km)</span></div>
                                           <div class="addr"><i class="bi bi-geo-alt-fill"></i> ${s.address}</div>
                                           <div class="price" style="color:${meta.color};">● ${meta.label}</div>
                                           ${first ? `<div class="price">₱${Number(first.price).toFixed(2)} / L ${first.fuel_type}</div>` : ''}`;
                        item.addEventListener('click', () => {
                            if (markers[s.id]) { map.setView([markers[s.id].getLatLng()], 17); markers[s.id].openPopup(); }
                        });
                        listEl.appendChild(item);
                    });
                });
        }, function () {
            alert('Unable to get your location. Please check your browser permissions.');
        });
    });
</script>
@endpush
@endsection
