@props(['instanceId' => 'gis', 'autoInit' => true, 'height' => 'clamp(320px, 60vh, 460px)', 'heatmapAvailable' => null, 'heatDefault' => null])
@php
    $id = $instanceId;
    // Public pages obey the LGU's settings; the LGU page forces both on.
    $heatOn = $heatmapAvailable ?? \App\Models\Setting::bool('heatmap_enabled', true);
    $heatStart = $heatOn && ($heatDefault ?? \App\Models\Setting::bool('heatmap_auto', true));
@endphp
<div class="gg-map-shell gg-gis-console" id="{{ $id }}Root">
    <div class="gg-gis-controls">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div>
                <label class="form-label small mb-0 d-block" for="{{ $id }}Fuel">Fuel Type</label>
                <select id="{{ $id }}Fuel" class="form-select form-select-sm"></select>
            </div>
            @if($heatOn)
            <div class="form-check form-switch m-0 pt-3">
                <input class="form-check-input" type="checkbox" role="switch" id="{{ $id }}Heat" @checked($heatStart)>
                <label class="form-check-label small fw-semibold" for="{{ $id }}Heat">Heatmap</label>
            </div>
            @endif
            <div class="form-check form-switch m-0 pt-3">
                <input class="form-check-input" type="checkbox" role="switch" id="{{ $id }}Stations" checked>
                <label class="form-check-label small fw-semibold" for="{{ $id }}Stations">Stations</label>
            </div>
            <div class="d-flex gap-2 pt-3 ms-md-auto">
                <button type="button" class="btn btn-sm btn-outline-brand" id="{{ $id }}Locate"><i class="bi bi-crosshair"></i> My Location</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#{{ $id }}More" aria-expanded="false"><i class="bi bi-sliders"></i> Filters</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="{{ $id }}Reset" title="Reset filters"><i class="bi bi-arrow-counterclockwise"></i></button>
            </div>
        </div>
        <div class="collapse mt-2" id="{{ $id }}More">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1" for="{{ $id }}Level">Price Level</label>
                    <select id="{{ $id }}Level" class="form-select form-select-sm">
                        <option value="">All Levels</option>
                        <option value="low">Low</option>
                        <option value="moderate">Moderate</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1" for="{{ $id }}Avail">Availability</label>
                    <select id="{{ $id }}Avail" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="enough">Has Enough</option>
                        <option value="almost_empty">Almost Empty</option>
                        <option value="no_fuel">No Gasoline</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1" for="{{ $id }}Brgy">Barangay</label>
                    <select id="{{ $id }}Brgy" class="form-select form-select-sm"><option value="">All Barangays</option></select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1" for="{{ $id }}Color">Color Markers By</label>
                    <select id="{{ $id }}Color" class="form-select form-select-sm">
                        <option value="availability">Fuel Availability</option>
                        <option value="price">Price Level</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small mb-1" for="{{ $id }}Hi">Highlight</label>
                    <select id="{{ $id }}Hi" class="form-select form-select-sm">
                        <option value="all">All Prices</option>
                        <option value="lowest">Lowest Price</option>
                        <option value="highest">Highest Price</option>
                        <option value="average">Closest to Average</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <div class="form-check small m-0">
                        <input class="form-check-input" type="checkbox" id="{{ $id }}Areas">
                        <label class="form-check-label" for="{{ $id }}Areas">Show Barangay Areas</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="gg-gis-body-inline">
        <div class="position-relative">
            <div id="{{ $id }}Map" class="gg-gis-map-inline" style="height: {{ $height }};"></div>
            <div class="gg-gis-legend-float">
                <div class="fw-bold" style="font-size:.7rem;">PRICE LEVEL <span class="text-muted-gg fw-normal">(relative)</span></div>
                <div class="gis-legend-row"><span class="dot" style="background:#15803D;"></span>Low</div>
                <div class="gis-legend-row"><span class="dot" style="background:#EAB308;"></span>Moderate</div>
                <div class="gis-legend-row"><span class="dot" style="background:#F58220;"></span>High</div>
                <div class="gis-legend-row"><span class="dot" style="background:#B91C1C;"></span>Highest</div>
                <div class="fw-bold mt-1" style="font-size:.7rem;">AVAILABILITY</div>
                <div class="gis-legend-row"><span class="dot" style="background:#15803D;"></span>Enough</div>
                <div class="gis-legend-row"><span class="dot" style="background:#B45309;"></span>Almost Empty</div>
                <div class="gis-legend-row"><span class="dot" style="background:#B91C1C;"></span>No Gasoline</div>
            </div>
        </div>
        <div class="gg-gis-summary-inline">
            <div id="{{ $id }}Summary"></div>
            <div id="{{ $id }}AreaPanel" class="d-none mt-2"></div>
            <p class="text-muted-gg mb-0 mt-2" style="font-size:.68rem;">Price levels compare stations against each other for the selected fuel. They are relative, not official classifications, and only describe recorded prices.</p>
        </div>
    </div>
</div>

@once
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.heat/0.2.0/leaflet-heat.js"></script>
@endpush
@endonce

@push('scripts')
<script>
(function () {
    var ID = {!! json_encode($id) !!};
    var DATA_URL = {!! json_encode(route('gis.data')) !!};
    var AUTO = {{ $autoInit ? 'true' : 'false' }};
    var HEAT_START = {{ $heatStart ? 'true' : 'false' }};
    var CENTER = [8.3696, 124.8642]; // Manolo Fortich, Bukidnon
    var AVAIL = {
        enough: { label: 'Has Enough Gasoline', color: '#15803D' },
        almost_empty: { label: 'Almost Empty', color: '#B45309' },
        no_fuel: { label: 'No Gasoline', color: '#B91C1C' },
        unknown: { label: 'No Data Yet', color: '#6B7280' }
    };
    var LEVEL_COLOR = { low: '#15803D', moderate: '#EAB308', high: '#F58220', highest: '#B91C1C' };

    function $(suffix) { return document.getElementById(ID + suffix); }
    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function ago(iso) {
        var s = Math.max(0, (Date.now() - new Date(iso).getTime()) / 1000);
        if (s < 90) return 'just now';
        if (s < 3600) return Math.round(s / 60) + ' minutes ago';
        if (s < 86400) return Math.round(s / 3600) + ' hours ago';
        return Math.round(s / 86400) + ' days ago';
    }
    function validCoord(s) {
        return typeof s.latitude === 'number' && typeof s.longitude === 'number' &&
            isFinite(s.latitude) && isFinite(s.longitude) && Math.abs(s.latitude) <= 90 && Math.abs(s.longitude) <= 180;
    }
    function priceFor(s, ftId) {
        return (s.current_prices_list || []).find(function (p) { return String(p.fuel_type_id) === String(ftId); });
    }
    function quartiles(values) {
        var a = values.slice().sort(function (x, y) { return x - y; });
        function q(p) { var i = (a.length - 1) * p, lo = Math.floor(i), hi = Math.ceil(i); return a[lo] + (a[hi] - a[lo]) * (i - lo); }
        return { q25: q(0.25), q50: q(0.5), q75: q(0.75) };
    }
    function levelFor(price, b) {
        if (price <= b.q25) return 'low';
        if (price <= b.q50) return 'moderate';
        if (price <= b.q75) return 'high';
        return 'highest';
    }
    function pinIcon(color, selected, logo) {
        // Station logo/photo sits inside the pin; if missing or broken the pump icon shows instead.
        var img = logo ? '<img src="' + esc(logo) + '" alt="" onerror="this.style.display=\'none\'">' : '';
        return L.divIcon({
            className: '',
            html: '<div class="gg-marker-pin' + (selected ? ' selected' : '') + '" style="background:' + color + ';"><i class="bi bi-fuel-pump-fill"></i>' + img + '</div>',
            iconSize: [28, 28], iconAnchor: [14, 28], popupAnchor: [0, -26]
        });
    }

    var map, stations = [], markerLayer, heatLayer, areaLayer, userMarker, userLatLng = null, markerById = {}, started = false;

    function distanceKm(a, b) {
        var R = 6371, dLat = (b[0] - a[0]) * Math.PI / 180, dLng = (b[1] - a[1]) * Math.PI / 180;
        var x = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(a[0] * Math.PI / 180) * Math.cos(b[0] * Math.PI / 180) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return R * 2 * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x));
    }

    function staleAll(s) {
        var l = s.current_prices_list || [];
        return l.length > 0 && l.every(function (p) { return p.stale; });
    }

    function popupHtml(s, focusFt) {
        var meta = AVAIL[s.availability] || AVAIL.unknown;
        var lines = (s.current_prices_list || []).map(function (p) {
            var m = AVAIL[p.availability_status] || AVAIL.unknown;
            var bold = String(p.fuel_type_id) === String(focusFt) ? ' style="background:#FFF1E6;border-radius:4px;padding:0 3px;"' : '';
            return '<div class="price-line"' + bold + '>' + esc(p.fuel_type.name) + ': <strong>&#8369;' + Number(p.price).toFixed(2) +
                '</strong> <span style="color:' + m.color + ';" title="' + esc(m.label) + '">&#9679;</span></div>';
        }).join('');
        var newest = (s.current_prices_list || []).reduce(function (acc, p) { return !acc || p.created_at > acc ? p.created_at : acc; }, null);
        var logo = s.logo_url ? '<img src="' + esc(s.logo_url) + '" alt="" style="width:28px;height:28px;border-radius:6px;object-fit:cover;vertical-align:middle;margin-right:6px;">' : '';
        var dist = userLatLng ? '<div class="text-muted-gg" style="font-size:.74rem;">&#128205; ' + distanceKm(userLatLng, [s.latitude, s.longitude]).toFixed(1) + ' km from you</div>' : '';
        var services = (s.active_services || []).slice(0, 4).map(esc).join(', ');
        return '<div class="gg-popup"><h6>' + logo + esc(s.station_name) + '</h6>' +
            '<div class="addr">' + esc(s.address) + ', ' + esc(s.barangay) + '</div>' + dist +
            '<div class="price-line" style="color:' + meta.color + ';font-weight:700;">&#9679; ' + esc(meta.label) + '</div>' +
            (lines || '<div class="text-muted-gg" style="font-size:.8rem;">No prices reported yet</div>') +
            (newest ? '<div class="text-muted-gg" style="font-size:.72rem;margin-top:2px;">Updated ' + esc(ago(newest)) + (staleAll(s) ? ' <span style="color:#B45309;font-weight:700;">&#9888; Price may be outdated</span>' : '') + '</div>' : '') +
            (services ? '<div class="text-muted-gg" style="font-size:.75rem;margin-top:4px;">Services: ' + services + '</div>' : '') +
            '<a href="/stations/' + encodeURIComponent(s.id) + '?highlight=' + encodeURIComponent(focusFt || '') + '">View Details &rarr;</a></div>';
    }

    function fillFilters() {
        var fuel = $('Fuel'), seen = {}, brgy = $('Brgy'), bs = {};
        fuel.innerHTML = '';
        stations.forEach(function (s) {
            (s.current_prices_list || []).forEach(function (p) {
                if (!seen[p.fuel_type_id]) {
                    seen[p.fuel_type_id] = 1;
                    var o = document.createElement('option');
                    o.value = p.fuel_type_id; o.textContent = p.fuel_type.name; fuel.appendChild(o);
                }
            });
            if (s.barangay && !bs[s.barangay]) {
                bs[s.barangay] = 1;
                var b = document.createElement('option'); b.value = s.barangay; b.textContent = s.barangay; brgy.appendChild(b);
            }
        });
    }

    function render() {
        if (!map) return;
        var ftId = $('Fuel').value, wantHeat = !!($('Heat') && $('Heat').checked), wantStations = $('Stations').checked, level = $('Level').value, avail = $('Avail').value;
        var brgy = $('Brgy').value, colorBy = $('Color').value, hi = $('Hi').value;

        var inScope = stations.filter(function (s) { return validCoord(s) && (!brgy || s.barangay === brgy); });
        var priced = inScope.map(function (s) { return { s: s, p: priceFor(s, ftId) }; }).filter(function (x) { return x.p; });
        var excluded = inScope.length - priced.length + (stations.length - stations.filter(validCoord).length);

        var prices = priced.map(function (x) { return x.p.price; });
        var bounds = prices.length ? quartiles(prices) : null;
        priced.forEach(function (x) { x.level = levelFor(x.p.price, bounds); });

        var shown = priced.filter(function (x) {
            if (level && x.level !== level && !(level === 'high' && x.level === 'highest')) return false;
            if (avail && x.p.availability_status !== avail) return false;
            return true;
        });

        var lo = null, up = null, avg = null;
        if (priced.length) {
            lo = priced.reduce(function (a, b) { return a.p.price <= b.p.price ? a : b; });
            up = priced.reduce(function (a, b) { return a.p.price >= b.p.price ? a : b; });
            avg = prices.reduce(function (a, b) { return a + b; }, 0) / prices.length;
        }
        var nearAvg = priced.length ? priced.reduce(function (a, b) { return Math.abs(a.p.price - avg) <= Math.abs(b.p.price - avg) ? a : b; }) : null;

        if (markerLayer) map.removeLayer(markerLayer);
        if (heatLayer) { map.removeLayer(heatLayer); heatLayer = null; }
        if (areaLayer) { map.removeLayer(areaLayer); areaLayer = null; }
        markerById = {};

        if (wantStations) {
            markerLayer = L.layerGroup();
            shown.forEach(function (x) {
                var color = colorBy === 'price' ? LEVEL_COLOR[x.level] : (AVAIL[x.p.availability_status] || AVAIL.unknown).color;
                var sel = (hi === 'lowest' && lo && x.s.id === lo.s.id) || (hi === 'highest' && up && x.s.id === up.s.id) || (hi === 'average' && nearAvg && x.s.id === nearAvg.s.id);
                var dim = (hi !== 'all' && !sel) || false;
                var m = L.marker([x.s.latitude, x.s.longitude], { icon: pinIcon(color, sel, x.s.logo_url), opacity: dim ? 0.4 : 1 }).bindPopup(popupHtml(x.s, ftId));
                markerLayer.addLayer(m);
                markerById[x.s.id] = m;
            });
            markerLayer.addTo(map);
        } else {
            markerLayer = null;
        }

        if (wantHeat && shown.length && typeof L.heatLayer === 'function') {
            var mn = Math.min.apply(null, prices), mx = Math.max.apply(null, prices);
            var pts = shown.map(function (x) {
                var n = mx > mn ? (x.p.price - mn) / (mx - mn) : 0.5;
                return [x.s.latitude, x.s.longitude, 0.25 + n * 0.75];
            });
            heatLayer = L.heatLayer(pts, { radius: 38, blur: 28, maxZoom: 15, max: 1.0, gradient: { 0: '#15803D', 0.35: '#EAB308', 0.65: '#F58220', 1: '#B91C1C' } }).addTo(map);
        }

        var areaPanel = $('AreaPanel');
        if ($('Areas').checked && priced.length) {
            var by = {};
            priced.forEach(function (x) {
                var k = x.s.barangay || 'Unknown';
                (by[k] = by[k] || { la: [], ln: [], pr: [] });
                by[k].la.push(x.s.latitude); by[k].ln.push(x.s.longitude); by[k].pr.push(x.p.price);
            });
            areaLayer = L.layerGroup();
            var html = '<div class="fw-bold small mb-1">AREA / BARANGAY</div>';
            Object.keys(by).forEach(function (k) {
                var d = by[k], sum = function (a) { return a.reduce(function (p, c) { return p + c; }, 0); };
                var la = sum(d.la) / d.la.length, ln = sum(d.ln) / d.ln.length;
                var mi = Math.min.apply(null, d.pr), ma = Math.max.apply(null, d.pr), av = sum(d.pr) / d.pr.length;
                L.circle([la, ln], { radius: 250 + d.pr.length * 60, color: '#181818', fillColor: '#F58220', fillOpacity: 0.15, weight: 1 })
                    .bindTooltip(k + ': avg &#8369;' + av.toFixed(2)).addTo(areaLayer);
                html += '<div class="gis-area-row"><div class="fw-semibold">' + esc(k) + '</div><div class="text-muted-gg" style="font-size:.74rem;">Low &#8369;' + mi.toFixed(2) +
                    ' &middot; Avg &#8369;' + av.toFixed(2) + ' &middot; High &#8369;' + ma.toFixed(2) + ' &middot; ' + d.pr.length + ' station' + (d.pr.length > 1 ? 's' : '') + '</div></div>';
            });
            areaLayer.addTo(map);
            areaPanel.innerHTML = html; areaPanel.classList.remove('d-none');
        } else {
            areaPanel.classList.add('d-none');
        }

        var sel = $('Fuel'), fname = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].textContent : 'Fuel';
        var latest = priced.reduce(function (acc, x) { return !acc || x.p.created_at > acc ? x.p.created_at : acc; }, null);
        $('Summary').innerHTML = priced.length ?
            '<div class="fw-bold small mb-1">' + esc(fname.toUpperCase()) + ' SUMMARY</div>' +
            '<div class="gis-area-row"><div class="text-muted-gg" style="font-size:.74rem;">Lowest</div><div class="fw-semibold" style="color:#15803D;">&#8369;' + lo.p.price.toFixed(2) + ' &mdash; ' + esc(lo.s.station_name) + '</div></div>' +
            '<div class="gis-area-row"><div class="text-muted-gg" style="font-size:.74rem;">Highest</div><div class="fw-semibold" style="color:#B91C1C;">&#8369;' + up.p.price.toFixed(2) + ' &mdash; ' + esc(up.s.station_name) + '</div></div>' +
            '<div class="gis-area-row"><div class="text-muted-gg" style="font-size:.74rem;">Average / Range</div><div class="fw-semibold">&#8369;' + avg.toFixed(2) + ' <span class="text-muted-gg fw-normal">(&#8369;' + lo.p.price.toFixed(2) + ' &ndash; &#8369;' + up.p.price.toFixed(2) + ')</span></div></div>' +
            '<div class="text-muted-gg" style="font-size:.72rem;">' + priced.length + ' station' + (priced.length > 1 ? 's' : '') + ' included' + (brgy ? ' in ' + esc(brgy) : '') +
            (shown.length !== priced.length ? ' &middot; ' + shown.length + ' match filters' : '') +
            (excluded > 0 ? ' &middot; ' + excluded + ' excluded (no price or location)' : '') +
            (priced.filter(function (x) { return x.p.stale; }).length ? ' &middot; ' + priced.filter(function (x) { return x.p.stale; }).length + ' with outdated prices (&#9888;)' : '') +
            (latest ? '<br>Latest update ' + esc(ago(latest)) : '') + '</div>'
            : '<p class="text-muted-gg small mb-0">No price data for this selection.</p>';
    }

    function setUser(lat, lng) {
        userLatLng = [lat, lng];
        if (!map) return;
        if (userMarker) map.removeLayer(userMarker);
        userMarker = L.circleMarker([lat, lng], { radius: 8, color: '#fff', weight: 3, fillColor: '#2563EB', fillOpacity: 1 }).addTo(map).bindPopup('You are here');
        map.setView([lat, lng], 14);
        render();
    }

    function init() {
        if (started) { if (map) map.invalidateSize(); return; }
        started = true;
        map = L.map(ID + 'Map', { scrollWheelZoom: true }).setView(CENTER, 13);
        var std = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors', maxZoom: 19 });
        // Esri's light-gray canvas: free, no API key (CARTO's basemaps now require one).
        var light = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', { attribution: 'Tiles &copy; Esri', maxNativeZoom: 16, maxZoom: 19 });
        std.addTo(map);
        L.control.layers({ 'Standard': std, 'Light': light }, null, { collapsed: true }).addTo(map);

        fetch(DATA_URL, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (rows) {
                stations = rows;
                fillFilters();
                ['Fuel', 'Heat', 'Stations', 'Level', 'Avail', 'Brgy', 'Color', 'Hi', 'Areas'].forEach(function (k) { if ($(k)) $(k).addEventListener('change', render); });
                $('Locate').addEventListener('click', function () {
                    if (!navigator.geolocation) return;
                    navigator.geolocation.getCurrentPosition(function (pos) { setUser(pos.coords.latitude, pos.coords.longitude); }, function () {});
                });
                $('Reset').addEventListener('click', function () {
                    ['Level', 'Avail', 'Brgy', 'Color', 'Hi'].forEach(function (k) { $(k).selectedIndex = 0; });
                    if ($('Heat')) $('Heat').checked = HEAT_START;
                    $('Stations').checked = true;
                    $('Areas').checked = false;
                    if ($('Fuel').options.length) $('Fuel').selectedIndex = 0;
                    map.setView(userLatLng || CENTER, userLatLng ? 14 : 13);
                    render();
                });
                render();
            })
            .catch(function () {
                $('Summary').innerHTML = '<p class="text-danger small mb-0">Could not load station data. Please refresh the page.</p>';
            });
        setTimeout(function () { map.invalidateSize(); }, 200);
    }

    window['gisInit_' + ID] = init;
    window['gisSetUserLocation_' + ID] = function (lat, lng) { if (!started) init(); if (map) setUser(lat, lng); else userLatLng = [lat, lng]; };
    window['gisFocusStation_' + ID] = function (sid) {
        var s = stations.find(function (x) { return x.id === sid; });
        if (!s || !map) return;
        map.setView([s.latitude, s.longitude], 16);
        if (markerById[sid]) markerById[sid].openPopup();
    };
    if (AUTO) {
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
    }
})();
</script>
@endpush
