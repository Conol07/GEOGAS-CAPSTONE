@extends('layouts.app')
@section('title', 'Station Map')

@section('content')
<h4 class="mb-1">Interactive Station Map</h4>
<p class="text-muted-gg mb-3">Locate participating gasoline stations across Manolo Fortich, Bukidnon.</p>

<div class="gg-map-shell">
    <div class="gg-map-controls">
        <div class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted-gg"></i></span>
                    <input type="text" id="mapSearch" class="form-control border-start-0" placeholder="Search stations">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select id="fuelTypeFilter" class="form-select form-select-sm">
                    @foreach($fuelTypes as $ft)
                        <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="availabilityFilter" class="form-select form-select-sm">
                    <option value="">Any Availability</option>
                    <option value="enough">🟢 Enough</option>
                    <option value="almost_empty">🟡 Almost Empty</option>
                    <option value="no_fuel">🔴 No Gasoline</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="barangayFilter" class="form-select form-select-sm">
                    <option value="">All Areas</option>
                    @foreach($barangays as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button id="nearMeBtn" type="button" class="btn btn-sm btn-outline-brand w-100"><i class="bi bi-crosshair"></i> Near Me</button>
            </div>
            <div class="col-md-1">
                <button id="moreFiltersBtn" type="button" class="btn btn-sm btn-outline-brand w-100" data-bs-toggle="collapse" data-bs-target="#moreFilters"><i class="bi bi-sliders"></i></button>
            </div>
        </div>
        <div class="collapse mt-2" id="moreFilters">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Price range (₱)</label>
                    <div class="d-flex gap-2">
                        <input type="number" id="priceMin" class="form-control form-control-sm" placeholder="Min">
                        <input type="number" id="priceMax" class="form-control form-control-sm" placeholder="Max">
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label small mb-1">Station services</label>
                    <div class="d-flex flex-wrap gap-2" id="serviceFilterList">
                        @foreach($services as $key => $label)
                            <label class="small border rounded-3 px-2 py-1" style="border-color:var(--gg-border) !important; cursor:pointer;">
                                <input type="checkbox" class="form-check-input me-1 service-filter" value="{{ $label }}" style="vertical-align:middle;">{{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
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

    function priceFor(station, fuelTypeId) {
        return (station.current_prices_list || []).find(p => String(p.fuel_type_id) === String(fuelTypeId));
    }

    function gasStationIcon(price, selected = false) {
        const priceTag = price ? `<div class="gg-marker-price">₱${Number(price.price).toFixed(2)}</div>` : '';
        return L.divIcon({
            className: '',
            html: `<div style="position:relative;"><div class="gg-marker-pin${selected ? ' selected' : ''}"><i class="bi bi-fuel-pump-fill"></i></div>${priceTag}</div>`,
            iconSize: [28, 28], iconAnchor: [14, 28], popupAnchor: [0, -26],
        });
    }

    const markers = {};
    let activeMarkerId = null;

    function setActiveMarker(id) {
        if (activeMarkerId !== null && markers[activeMarkerId]) {
            const s = allStations.find(x => x.id === activeMarkerId);
            markers[activeMarkerId].setIcon(gasStationIcon(priceFor(s, document.getElementById('fuelTypeFilter').value), false));
        }
        const s = allStations.find(x => x.id === id);
        markers[id].setIcon(gasStationIcon(priceFor(s, document.getElementById('fuelTypeFilter').value), true));
        activeMarkerId = id;
    }

    function popupHtml(s) {
        const meta = availabilityMeta[s.availability] || availabilityMeta.unknown;
        const priceLines = (s.current_prices_list || []).map(p => {
            const pMeta = availabilityMeta[p.availability_status] || availabilityMeta.unknown;
            return `<div class="price-line">${p.fuel_type.name}: <strong>₱${Number(p.price).toFixed(2)}</strong> <span style="color:${pMeta.color};">●</span></div>`;
        }).join('');
        const services = (s.active_services || []).join(', ');
        return `<div class="gg-popup">
                    <h6>${s.station_name}</h6>
                    <div class="addr">${s.address}, ${s.barangay}</div>
                    <div class="price-line" style="color:${meta.color}; font-weight:700;">● ${meta.label}</div>
                    ${priceLines || '<div class="text-muted-gg" style="font-size:.8rem;">No prices reported yet</div>'}
                    ${services ? `<div class="text-muted-gg" style="font-size:.75rem; margin-top:4px;">Services: ${services}</div>` : ''}
                    <a href="/stations/${s.id}">View Details →</a>
                </div>`;
    }

    allStations.forEach(s => {
        // fuel_type_id isn't in the price payload by default — tag it client-side from the price list order
        s.current_prices_list.forEach(p => { p.fuel_type_id = p.fuel_type_id ?? p.fuel_type.id ?? null; });
        markers[s.id] = L.marker([s.latitude, s.longitude], { icon: gasStationIcon(null, false) }).addTo(map).bindPopup(popupHtml(s));
        markers[s.id].on('click', () => setActiveMarker(s.id));
    });

    const listEl = document.getElementById('mapStationList');

    function renderList(filtered) {
        listEl.innerHTML = '';
        const fuelTypeId = document.getElementById('fuelTypeFilter').value;
        filtered.forEach(s => {
            const meta = availabilityMeta[s.availability] || availabilityMeta.unknown;
            const price = priceFor(s, fuelTypeId);
            const item = document.createElement('div');
            item.className = 'gg-map-list-item';
            item.innerHTML = `<div class="name">${s.station_name}</div>
                               <div class="addr"><i class="bi bi-geo-alt-fill"></i> ${s.address}, ${s.barangay}</div>
                               <div class="price" style="color:${meta.color};">● ${meta.label}</div>
                               ${price ? `<div class="price">₱${Number(price.price).toFixed(2)} / L</div>` : ''}`;
            item.addEventListener('click', () => {
                map.setView([s.latitude, s.longitude], 17);
                setActiveMarker(s.id);
                markers[s.id].openPopup();
            });
            listEl.appendChild(item);
        });
    }

    function applyFilters() {
        const term = document.getElementById('mapSearch').value.toLowerCase();
        const availability = document.getElementById('availabilityFilter').value;
        const barangay = document.getElementById('barangayFilter').value;
        const fuelTypeId = document.getElementById('fuelTypeFilter').value;
        const priceMin = parseFloat(document.getElementById('priceMin').value);
        const priceMax = parseFloat(document.getElementById('priceMax').value);
        const selectedServices = Array.from(document.querySelectorAll('.service-filter:checked')).map(el => el.value);

        const filtered = allStations.filter(s => {
            const matchesTerm = s.station_name.toLowerCase().includes(term) || s.address.toLowerCase().includes(term);
            const matchesAvailability = !availability || s.availability === availability;
            const matchesBarangay = !barangay || s.barangay === barangay;
            const price = priceFor(s, fuelTypeId);
            const matchesMin = isNaN(priceMin) || (price && price.price >= priceMin);
            const matchesMax = isNaN(priceMax) || (price && price.price <= priceMax);
            const matchesServices = selectedServices.every(svc => (s.active_services || []).includes(svc));
            return matchesTerm && matchesAvailability && matchesBarangay && matchesMin && matchesMax && matchesServices;
        });

        renderList(filtered);
        Object.values(markers).forEach(m => map.removeLayer(m));
        filtered.forEach(s => {
            const price = priceFor(s, fuelTypeId);
            markers[s.id].setIcon(gasStationIcon(price, s.id === activeMarkerId));
            markers[s.id].addTo(map);
        });
    }

    ['mapSearch', 'availabilityFilter', 'barangayFilter', 'fuelTypeFilter', 'priceMin', 'priceMax'].forEach(id => {
        document.getElementById(id).addEventListener('input', applyFilters);
    });
    document.querySelectorAll('.service-filter').forEach(el => el.addEventListener('change', applyFilters));

    applyFilters(); // initial render

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
                            if (markers[s.id]) { map.setView(markers[s.id].getLatLng(), 17); markers[s.id].openPopup(); }
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
