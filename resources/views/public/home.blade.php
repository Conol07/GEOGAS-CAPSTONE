@extends('layouts.app')
@section('title', 'Home')

@section('content')
{{-- HERO --}}
<div class="gg-dash-hero mb-4">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <h1>Find Fuel<br><span class="accent">Near You</span></h1>
            <p class="mb-4">Real-time fuel prices. Nearby stations. A more informed Manolo Fortich.</p>
            <button id="enableLocationBtn" class="btn btn-brand btn-lg"><i class="bi bi-geo-alt-fill me-1"></i>Activate My Location</button>
            <p class="small mt-2 mb-0" style="color:#C9C9C9;">Find the nearest gasoline stations around you. Completely optional.</p>
        </div>
        <div class="col-lg-5">
            <div class="gg-dash-stats">
                <div class="small mb-2" style="color:#D1D1D1;"><i class="bi bi-geo-alt-fill me-1"></i>Manolo Fortich, Bukidnon</div>
                <div class="row g-3 text-center">
                    <div class="col-12">
                        <div class="stat-value">{{ $stats['total_stations'] }}</div>
                        <div class="stat-label">Participating Stations</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- PRICE OVERVIEW --}}
@if($priceOverview->isNotEmpty())
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Lowest Prices</div>
            <div class="card-body">
                @foreach($priceOverview as $row)
                    <a href="{{ route('stations.show', $row['lowest']->station) }}?highlight={{ $row['fuel_type']->id }}" class="gg-price-overview-row">
                        <div>
                            <div class="fw-semibold small">{{ $row['fuel_type']->name }}</div>
                            <div class="text-muted-gg" style="font-size:.75rem;">{{ $row['lowest']->station->station_name }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold" style="color:#15803D;">₱{{ number_format($row['lowest']->price,2) }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Highest Prices</div>
            <div class="card-body">
                @foreach($priceOverview as $row)
                    <a href="{{ route('stations.show', $row['highest']->station) }}?highlight={{ $row['fuel_type']->id }}" class="gg-price-overview-row">
                        <div>
                            <div class="fw-semibold small">{{ $row['fuel_type']->name }}</div>
                            <div class="text-muted-gg" style="font-size:.75rem;">{{ $row['highest']->station->station_name }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold" style="color:#B91C1C;">₱{{ number_format($row['highest']->price,2) }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- MAP + NEARBY + UPDATES --}}
<div class="row g-3">
    <div class="col-lg-5">
        <div class="gg-map-shell h-100">
            <div class="gg-map-controls d-flex justify-content-between align-items-center">
                <span class="small fw-semibold text-muted-gg"><i class="bi bi-map me-1"></i>Gasoline Stations Around You</span>
                <button type="button" class="small border-0 bg-transparent" style="color:var(--gg-accent);" data-bs-toggle="modal" data-bs-target="#largerMapModal">View Larger Map</button>
            </div>
            <div id="homeMap" class="gg-map-canvas" style="height:340px;"></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="gg-location-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="bi bi-signpost-split-fill me-1" style="color:var(--gg-accent);"></i>Nearby Gasoline Stations</h6>
                <a href="{{ route('stations.index') }}" class="small">See All</a>
            </div>
            <p class="text-muted-gg small mb-2" id="nearbyHint">Showing a few participating stations. Activate your location to sort by distance.</p>
            <div id="nearbyList">
                @foreach($previewStations as $s)
                    <div class="gg-dash-station-row" data-station-id="{{ $s->id }}">
                        <x-station-logo :station="$s" :size="40" />
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $s->station_name }}</div>
                            <div class="text-muted-gg" style="font-size:.72rem;">{{ $s->barangay }}</div>
                            @foreach($s->current_prices_list->take(2) as $p)
                                <div class="gg-dash-price-row">
                                    <span>{{ $p->fuelType->name }}</span>
                                    <span>₱{{ number_format($p->price,2) }} <span style="color:{{ ['enough'=>'#15803D','almost_empty'=>'#B45309','no_fuel'=>'#B91C1C'][$p->availability_status] ?? '#6B7280' }};">●</span></span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="gg-location-card mb-3">
            <h6 class="mb-3"><i class="bi bi-bell-fill me-1" style="color:var(--gg-accent);"></i>Latest Updates</h6>
            @forelse($updates as $u)
                @php
                    $meta = match($u['type']) {
                        'no_fuel' => ['icon'=>'bi-x-circle-fill','bg'=>'#FEF2F2','fg'=>'#B91C1C','title'=>'No Fuel'],
                        'almost_empty' => ['icon'=>'bi-exclamation-triangle-fill','bg'=>'#FFFBEB','fg'=>'#B45309','title'=>'Almost Empty'],
                        default => ['icon'=>'bi-cash-coin','bg'=>'#FFF1E6','fg'=>'#F58220','title'=>'Price Update'],
                    };
                    $text = match($u['type']) {
                        'no_fuel' => "{$u['station']} has no {$u['fuel_type']} available.",
                        'almost_empty' => "{$u['station']} {$u['fuel_type']} is almost empty.",
                        default => "{$u['station']} updated their {$u['fuel_type']} price to ₱".number_format($u['price'],2).".",
                    };
                @endphp
                <div class="gg-update-card">
                    <div class="icon-badge" style="background:{{ $meta['bg'] }}; color:{{ $meta['fg'] }};"><i class="bi {{ $meta['icon'] }}"></i></div>
                    <div>
                        <div class="fw-semibold" style="font-size:.8rem;">{{ $meta['title'] }}</div>
                        <div class="text-muted-gg" style="font-size:.78rem;">{{ $text }}</div>
                        <div class="text-muted-gg" style="font-size:.7rem;">{{ $u['when'] }}</div>
                    </div>
                </div>
            @empty
                <p class="text-muted-gg small mb-0">No recent activity yet.</p>
            @endforelse
        </div>
        <div class="gg-tip-card">
            <i class="bi bi-lightbulb-fill me-1" style="color:var(--gg-accent);"></i>
            <strong>Tip:</strong> Enable your location to get the most accurate and nearest gasoline stations.
        </div>
    </div>
</div>

{{-- Larger map modal --}}
<div class="modal fade" id="largerMapModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gasoline Stations Around You</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="largerMap" style="height:70vh;"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const homeStations = @json($stations);

    const availabilityMeta = {
        enough: { label: 'Has Enough Gasoline', color: '#15803D' },
        almost_empty: { label: 'Almost Empty', color: '#B45309' },
        no_fuel: { label: 'No Gasoline', color: '#B91C1C' },
        unknown: { label: 'No Data Yet', color: '#6B7280' },
    };

    function gasIcon(selected = false) {
        return L.divIcon({
            className: '',
            html: `<div class="gg-marker-pin${selected ? ' selected' : ''}"><i class="bi bi-fuel-pump-fill"></i></div>`,
            iconSize: [28, 28], iconAnchor: [14, 28], popupAnchor: [0, -26],
        });
    }

    function popupHtml(s) {
        const meta = availabilityMeta[s.availability] || availabilityMeta.unknown;
        const priceLines = (s.current_prices_list || []).map(p => {
            const pMeta = availabilityMeta[p.availability_status] || availabilityMeta.unknown;
            return `<div class="price-line">${p.fuel_type.name}: <strong>₱${Number(p.price).toFixed(2)}</strong> <span style="color:${pMeta.color};">●</span></div>`;
        }).join('');
        const services = (s.active_services || []).slice(0, 4).join(', ');
        const lastUpdated = (s.current_prices_list && s.current_prices_list.length)
            ? new Date(s.current_prices_list[0].created_at).toLocaleString() : null;
        const logoImg = s.logo_url ? `<img src="${s.logo_url}" style="width:28px;height:28px;border-radius:6px;object-fit:cover;vertical-align:middle;margin-right:6px;">` : '';
        return `<div class="gg-popup">
                    <h6>${logoImg}${s.station_name}</h6>
                    <div class="addr">${s.address}, ${s.barangay}</div>
                    <div class="price-line" style="color:${meta.color}; font-weight:700;">● ${meta.label}</div>
                    ${priceLines || '<div class="text-muted-gg" style="font-size:.8rem;">No prices reported yet</div>'}
                    ${lastUpdated ? `<div class="text-muted-gg" style="font-size:.72rem; margin-top:2px;">Updated: ${lastUpdated}</div>` : ''}
                    ${services ? `<div class="text-muted-gg" style="font-size:.75rem; margin-top:4px;">Services: ${services}</div>` : ''}
                    <a href="/stations/${s.id}">View Details →</a>
                </div>`;
    }

    function buildMap(elementId) {
        const map = L.map(elementId).setView([8.3696, 124.8642], 13); // centered on Manolo Fortich, Bukidnon
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
        const markers = {};
        homeStations.forEach(s => {
            markers[s.id] = L.marker([s.latitude, s.longitude], { icon: gasIcon(false) }).addTo(map).bindPopup(popupHtml(s));
        });
        return { map, markers };
    }

    const homeMapInstance = buildMap('homeMap');
    let largerMapInstance = null;

    document.getElementById('largerMapModal').addEventListener('shown.bs.modal', function () {
        if (!largerMapInstance) {
            largerMapInstance = buildMap('largerMap');
        }
        largerMapInstance.map.invalidateSize();
    });

    function focusStation(id) {
        const s = homeStations.find(x => x.id === id);
        if (!s || !homeMapInstance.markers[id]) return;
        homeMapInstance.map.setView([s.latitude, s.longitude], 16);
        homeMapInstance.markers[id].openPopup();
    }

    document.getElementById('nearbyList').addEventListener('click', function (e) {
        const row = e.target.closest('[data-station-id]');
        if (row) focusStation(parseInt(row.dataset.stationId, 10));
    });

    // ---- Activate My Location ----
    document.getElementById('enableLocationBtn').addEventListener('click', function () {
        if (!navigator.geolocation) { alert('Geolocation is not supported by your browser.'); return; }
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Locating...';

        navigator.geolocation.getCurrentPosition(function (pos) {
            const { latitude, longitude } = pos.coords;
            homeMapInstance.map.setView([latitude, longitude], 14);
            L.marker([latitude, longitude]).addTo(homeMapInstance.map).bindPopup('You are here').openPopup();
            if (largerMapInstance) {
                largerMapInstance.map.setView([latitude, longitude], 14);
                L.marker([latitude, longitude]).addTo(largerMapInstance.map).bindPopup('You are here');
            }

            fetch(`{{ route('nearest') }}?lat=${latitude}&lng=${longitude}`)
                .then(r => r.json())
                .then(renderNearby)
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Location Active';
                });
        }, function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-geo-alt-fill me-1"></i>Activate My Location';
            alert('Unable to get your location. You can still use GeoGas ManFort normally without it.');
        });
    });

    function renderNearby(stations) {
        document.getElementById('nearbyHint').textContent = 'Sorted by distance from your current location.';
        const nearbyList = document.getElementById('nearbyList');
        nearbyList.innerHTML = '';
        stations.forEach(s => {
            const meta = availabilityMeta[s.availability] || availabilityMeta.unknown;
            const priceLines = (s.prices || []).slice(0, 2).map(p => {
                const pMeta = availabilityMeta[p.availability_status] || availabilityMeta.unknown;
                return `<div class="gg-dash-price-row"><span>${p.fuel_type}</span><span>₱${p.price.toFixed(2)} <span style="color:${pMeta.color};">●</span></span></div>`;
            }).join('');
            const logo = s.logo_url
                ? `<img src="${s.logo_url}" style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:1px solid #E5E5E5;flex-shrink:0;">`
                : `<div style="width:40px;height:40px;border-radius:10px;background:#FFF1E6;color:#F58220;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-fuel-pump-fill"></i></div>`;
            const row = document.createElement('div');
            row.className = 'gg-dash-station-row';
            row.innerHTML = `${logo}
                              <div class="flex-grow-1">
                                <div class="fw-semibold small">${s.station_name}</div>
                                <div class="text-muted-gg" style="font-size:.72rem;"><i class="bi bi-geo-alt-fill"></i> ${s.distance_km} km away &middot; ${s.barangay}</div>
                                ${priceLines}
                              </div>`;
            row.addEventListener('click', () => focusStation(s.id));
            nearbyList.appendChild(row);
        });
    }
</script>
@endpush
@endsection
