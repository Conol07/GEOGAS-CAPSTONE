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

@if($latestNews->isNotEmpty())
<div class="d-flex justify-content-between align-items-center mb-2 mt-4">
    <h6 class="mb-0"><i class="bi bi-megaphone-fill me-1" style="color:var(--gg-accent);"></i>Latest News</h6>
    <a href="{{ route('news.index') }}" class="small">View All News</a>
</div>
<div class="row g-3 mb-4">
    @foreach($latestNews as $n)
        <div class="col-md-4">
            <x-news-card :news="$n" />
        </div>
    @endforeach
</div>
@endif

{{-- Larger map modal — full GIS console --}}
<div class="modal fade" id="largerMapModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-map me-1"></i>GIS Fuel Price Map — Manolo Fortich</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="gg-gis-controls">
                    <div class="row g-2 align-items-center">
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Fuel Type</label>
                            <select id="gisFuelType" class="form-select form-select-sm"></select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Color Markers By</label>
                            <select id="gisColorMode" class="form-select form-select-sm">
                                <option value="availability">Fuel Availability</option>
                                <option value="price">Price Level</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Price Analysis</label>
                            <select id="gisPriceAnalysis" class="form-select form-select-sm">
                                <option value="all">All Prices</option>
                                <option value="lowest">Lowest Price</option>
                                <option value="highest">Highest Price</option>
                                <option value="average">Average Price</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small mb-1">Barangay</label>
                            <select id="gisBarangay" class="form-select form-select-sm">
                                <option value="">All Barangays</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small mb-1 d-block">Layers</label>
                            <div class="d-flex flex-wrap gap-2">
                                <div class="form-check form-check-inline small m-0">
                                    <input class="form-check-input gis-layer-toggle" type="checkbox" id="layerHeatmap">
                                    <label class="form-check-label" for="layerHeatmap">Heatmap</label>
                                </div>
                                <div class="form-check form-check-inline small m-0">
                                    <input class="form-check-input gis-layer-toggle" type="checkbox" id="layerAreas">
                                    <label class="form-check-label" for="layerAreas">Barangay Areas</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="gg-gis-body">
                    <div id="largerMap" class="gg-gis-map"></div>
                    <div class="gg-gis-sidebar">
                        <div class="gg-gis-legend mb-3">
                            <div class="fw-bold small mb-2">PRICE LEVEL <span class="text-muted-gg fw-normal">(vs. FUEL AVAILABILITY below)</span></div>
                            <div class="gis-legend-row"><span class="dot" style="background:#15803D;"></span>Low Price</div>
                            <div class="gis-legend-row"><span class="dot" style="background:#EAB308;"></span>Moderate Price</div>
                            <div class="gis-legend-row"><span class="dot" style="background:#F58220;"></span>High Price</div>
                            <div class="gis-legend-row"><span class="dot" style="background:#B91C1C;"></span>Highest Price</div>
                            <hr class="my-2">
                            <div class="fw-bold small mb-2">FUEL AVAILABILITY</div>
                            <div class="gis-legend-row"><span class="dot" style="background:#15803D;"></span>Has Enough Gasoline</div>
                            <div class="gis-legend-row"><span class="dot" style="background:#B45309;"></span>Almost Empty</div>
                            <div class="gis-legend-row"><span class="dot" style="background:#B91C1C;"></span>No Gasoline</div>
                        </div>
                        <div id="gisSummary" class="gg-gis-summary mb-3"></div>
                        <div id="gisAreaAnalysis" class="gg-gis-summary d-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.heat/0.2.0/leaflet-heat.js"></script>
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

    // ================= GIS console (the "View Larger Map" modal) =================
    let gisMap = null;
    let gisMarkersLayer = null;
    let gisHeatLayer = null;
    let gisAreaLayer = null;

    const priceLevelColors = { low: '#15803D', moderate: '#EAB308', high: '#F58220', highest: '#B91C1C' };

    function priceLevelIcon(color, selected = false) {
        return L.divIcon({
            className: '',
            html: `<div class="gg-marker-pin${selected ? ' selected' : ''}" style="background:${color};"><i class="bi bi-fuel-pump-fill"></i></div>`,
            iconSize: [28, 28], iconAnchor: [14, 28], popupAnchor: [0, -26],
        });
    }

    // Dynamic quartile classification — never a manual station→color assignment.
    function classifyPrices(prices) {
        const sorted = [...prices].sort((a, b) => a - b);
        const q = p => {
            const idx = (sorted.length - 1) * p;
            const lo = Math.floor(idx), hi = Math.ceil(idx);
            return sorted[lo] + (sorted[hi] - sorted[lo]) * (idx - lo);
        };
        return { q25: q(0.25), q50: q(0.5), q75: q(0.75) };
    }
    function levelFor(price, bounds) {
        if (price <= bounds.q25) return 'low';
        if (price <= bounds.q50) return 'moderate';
        if (price <= bounds.q75) return 'high';
        return 'highest';
    }

    function priceForFuel(station, fuelTypeId) {
        return (station.current_prices_list || []).find(p => String(p.fuel_type_id) === String(fuelTypeId));
    }

    function populateGisFilters() {
        const fuelSelect = document.getElementById('gisFuelType');
        const seen = new Set();
        homeStations.forEach(s => (s.current_prices_list || []).forEach(p => {
            if (!seen.has(p.fuel_type_id)) {
                seen.add(p.fuel_type_id);
                const opt = document.createElement('option');
                opt.value = p.fuel_type_id;
                opt.textContent = p.fuel_type.name;
                fuelSelect.appendChild(opt);
            }
        }));

        const barangaySelect = document.getElementById('gisBarangay');
        [...new Set(homeStations.map(s => s.barangay))].sort().forEach(b => {
            const opt = document.createElement('option');
            opt.value = b; opt.textContent = b;
            barangaySelect.appendChild(opt);
        });
    }

    function renderGisMap() {
        if (!gisMap) return;

        const fuelTypeId = document.getElementById('gisFuelType').value;
        const colorMode = document.getElementById('gisColorMode').value;
        const priceAnalysis = document.getElementById('gisPriceAnalysis').value;
        const barangay = document.getElementById('gisBarangay').value;

        const filtered = homeStations.filter(s => !barangay || s.barangay === barangay);
        const priced = filtered.map(s => ({ station: s, price: priceForFuel(s, fuelTypeId) })).filter(x => x.price);
        const prices = priced.map(x => parseFloat(x.price.price));
        const bounds = prices.length ? classifyPrices(prices) : null;

        let lowestEntry = null, highestEntry = null, avg = null;
        if (priced.length) {
            lowestEntry = priced.reduce((a, b) => (parseFloat(a.price.price) <= parseFloat(b.price.price) ? a : b));
            highestEntry = priced.reduce((a, b) => (parseFloat(a.price.price) >= parseFloat(b.price.price) ? a : b));
            avg = prices.reduce((a, b) => a + b, 0) / prices.length;
        }

        // Markers
        if (gisMarkersLayer) gisMap.removeLayer(gisMarkersLayer);
        gisMarkersLayer = L.layerGroup();

        priced.forEach(({ station: s, price: p }) => {
            let color, selected = false;
            if (colorMode === 'price' && bounds) {
                color = priceLevelColors[levelFor(parseFloat(p.price), bounds)];
            } else {
                color = (availabilityMeta[p.availability_status] || availabilityMeta.unknown).color;
            }
            if (priceAnalysis === 'lowest' && lowestEntry && s.id === lowestEntry.station.id) selected = true;
            if (priceAnalysis === 'highest' && highestEntry && s.id === highestEntry.station.id) selected = true;
            const dimmed = (priceAnalysis === 'lowest' || priceAnalysis === 'highest') && !selected;

            const marker = L.marker([s.latitude, s.longitude], {
                icon: priceLevelIcon(color, selected),
                opacity: dimmed ? 0.35 : 1,
            }).bindPopup(popupHtml(s));
            gisMarkersLayer.addLayer(marker);
        });
        gisMarkersLayer.addTo(gisMap);

        // Heatmap (intensity = normalized price for the selected fuel type)
        if (gisHeatLayer) { gisMap.removeLayer(gisHeatLayer); gisHeatLayer = null; }
        if (document.getElementById('layerHeatmap').checked && prices.length && typeof L.heatLayer === 'function') {
            const min = Math.min(...prices), max = Math.max(...prices);
            const points = priced.map(({ station: s, price: p }) => {
                const norm = max > min ? (parseFloat(p.price) - min) / (max - min) : 0.5;
                return [s.latitude, s.longitude, 0.3 + norm * 0.7];
            });
            gisHeatLayer = L.heatLayer(points, { radius: 35, blur: 25, maxZoom: 15 }).addTo(gisMap);
        }

        // Barangay area layer + analysis panel
        if (gisAreaLayer) { gisMap.removeLayer(gisAreaLayer); gisAreaLayer = null; }
        const areaPanel = document.getElementById('gisAreaAnalysis');
        if (document.getElementById('layerAreas').checked) {
            const byBarangay = {};
            priced.forEach(({ station: s, price: p }) => {
                byBarangay[s.barangay] = byBarangay[s.barangay] || { lats: [], lngs: [], prices: [] };
                byBarangay[s.barangay].lats.push(s.latitude);
                byBarangay[s.barangay].lngs.push(s.longitude);
                byBarangay[s.barangay].prices.push(parseFloat(p.price));
            });

            gisAreaLayer = L.layerGroup();
            let panelHtml = '<div class="fw-bold small mb-2">AREA / BARANGAY ANALYSIS</div>';
            Object.entries(byBarangay).forEach(([name, d]) => {
                const lat = d.lats.reduce((a, b) => a + b, 0) / d.lats.length;
                const lng = d.lngs.reduce((a, b) => a + b, 0) / d.lngs.length;
                const lo = Math.min(...d.prices), hi = Math.max(...d.prices);
                const av = d.prices.reduce((a, b) => a + b, 0) / d.prices.length;
                L.circle([lat, lng], {
                    radius: 250 + d.prices.length * 60,
                    color: '#181818', fillColor: '#F58220', fillOpacity: 0.15, weight: 1,
                }).bindTooltip(`${name}: ₱${av.toFixed(2)} avg (${d.prices.length} station${d.prices.length>1?'s':''})`).addTo(gisAreaLayer);

                panelHtml += `<div class="gis-area-row">
                                <div class="fw-semibold">${name}</div>
                                <div class="text-muted-gg" style="font-size:.75rem;">Lowest ₱${lo.toFixed(2)} &middot; Avg ₱${av.toFixed(2)} &middot; Highest ₱${hi.toFixed(2)} &middot; ${d.prices.length} station${d.prices.length>1?'s':''}</div>
                              </div>`;
            });
            gisAreaLayer.addTo(gisMap);
            areaPanel.innerHTML = panelHtml;
            areaPanel.classList.remove('d-none');
        } else {
            areaPanel.classList.add('d-none');
        }

        // Summary panel
        const fuelName = fuelSelectLabel();
        document.getElementById('gisSummary').innerHTML = priced.length ? `
            <div class="fw-bold small mb-2">${fuelName.toUpperCase()} PRICE SUMMARY</div>
            <div class="gis-area-row"><div>Lowest</div><div class="fw-semibold" style="color:#15803D;">₱${parseFloat(lowestEntry.price.price).toFixed(2)} — ${lowestEntry.station.station_name}</div></div>
            <div class="gis-area-row"><div>Highest</div><div class="fw-semibold" style="color:#B91C1C;">₱${parseFloat(highestEntry.price.price).toFixed(2)} — ${highestEntry.station.station_name}</div></div>
            <div class="gis-area-row"><div>Average</div><div class="fw-semibold">₱${avg.toFixed(2)}</div></div>
            <div class="text-muted-gg" style="font-size:.72rem;">Across ${priced.length} station${priced.length>1?'s':''}${barangay ? ' in ' + barangay : ''}</div>
        ` : '<p class="text-muted-gg small mb-0">No price data for this selection.</p>';
    }

    function fuelSelectLabel() {
        const sel = document.getElementById('gisFuelType');
        return sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].textContent : 'Fuel';
    }

    document.getElementById('largerMapModal').addEventListener('shown.bs.modal', function () {
        if (!gisMap) {
            gisMap = L.map('largerMap').setView([8.3696, 124.8642], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(gisMap);
            populateGisFilters();
            ['gisFuelType', 'gisColorMode', 'gisPriceAnalysis', 'gisBarangay'].forEach(id => {
                document.getElementById(id).addEventListener('change', renderGisMap);
            });
            document.querySelectorAll('.gis-layer-toggle').forEach(el => el.addEventListener('change', renderGisMap));
            renderGisMap();
        }
        gisMap.invalidateSize();
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
            if (gisMap) {
                gisMap.setView([latitude, longitude], 14);
                L.marker([latitude, longitude]).addTo(gisMap).bindPopup('You are here');
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
