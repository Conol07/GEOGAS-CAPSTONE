@extends('layouts.app')
@section('title', 'Distance & Travel Time')

@section('content')
<div class="mb-3">
    <h3 class="mb-1"><i class="bi bi-signpost-split-fill me-2" style="color:var(--gg-accent);"></i>Distance &amp; Travel Time</h3>
    <p class="text-muted-gg mb-0">See how many kilometers and minutes away each station is from where you are.</p>
</div>

<div class="card mb-3"><div class="card-body py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="form-check form-switch mb-0">
        <input class="form-check-input" type="checkbox" role="switch" id="tcSwitch" checked>
        <label class="form-check-label fw-semibold" for="tcSwitch">Show distance and time</label>
    </div>
    <span class="small text-muted-gg">Turn off to just browse the station map.</span>
</div></div>

<div class="row g-3 align-items-start">
    <div class="col-lg-5" id="tcLeft">
        <div class="card">
            <div class="card-body">
                <div id="tcInputs">
                    <div class="tc-step"><span class="tc-num">1</span> How are you traveling?</div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small mb-1" for="tcMode">Travel by</label>
                            <select id="tcMode" class="form-select">
                                <option value="30">Motorcycle / car</option>
                                <option value="15">Bicycle</option>
                                <option value="5">Walking</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1" for="tcFuel">Fuel type (price)</label>
                            <select id="tcFuel" class="form-select"></select>
                        </div>
                    </div>

                    <div class="tc-step"><span class="tc-num">2</span> Where are you starting?</div>
                    <div class="d-grid gap-2 mb-2" style="grid-template-columns: 1fr 1fr;">
                        <button type="button" id="tcLocate" class="btn btn-brand btn-sm"><i class="bi bi-crosshair me-1"></i>Use my location</button>
                        <select id="tcBrgy" class="form-select form-select-sm" aria-label="Choose a barangay"><option value="">Pick a barangay</option></select>
                    </div>
                </div>
                <div id="tcMap" class="tc-map"></div>
                <div class="form-text" id="tcOriginText" data-default="Or tap the map to drop a pin. You can drag it to adjust.">Or tap the map to drop a pin. You can drag it to adjust.</div>
            </div>
        </div>
    </div>

    <div class="col-lg-7" id="tcResults">
        <div id="tcBest"></div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center"><span>Stations near you</span><span class="small text-muted-gg fw-normal">closest first</span></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table gg-table align-middle mb-0">
                        <thead>
                            <tr><th>#</th><th>Station</th><th class="text-end">Distance</th><th class="text-end">Time</th><th class="text-end">Price</th><th></th></tr>
                        </thead>
                        <tbody id="tcRows"><tr><td colspan="6" class="text-center text-muted-gg py-4">Loading stations...</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <span class="gg-live"><span class="dot"></span><span id="tcLiveText">Prices update automatically</span></span>
            <span class="small text-muted-gg">Distance is estimated along roads (straight line &times; 1.3). Time uses an average speed, so traffic may vary.</span>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.tc-step { display:flex; align-items:center; gap:.5rem; font-weight:700; margin-bottom:.6rem; }
.tc-num { width:24px; height:24px; border-radius:50%; background:var(--gg-accent); color:#fff; font-size:.8rem; display:inline-flex; align-items:center; justify-content:center; flex:none; }
.tc-map { height:220px; border-radius:10px; overflow:hidden; border:1px solid var(--gg-border); }
@media (min-width: 992px) { #tcLeft { position: sticky; top: 84px; } }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var DATA_URL = {!! json_encode(route('gis.data')) !!};
    var ROAD = 1.3, CENTER = [8.3696, 124.8642];
    var stations = [], origin = null, map, originMarker, layer, on = true;
    var $ = function (id) { return document.getElementById(id); };
    function esc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function peso(n) { return '&#8369;' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function dist(a, b) {
        var R = 6371, dLat = (b[0] - a[0]) * Math.PI / 180, dLng = (b[1] - a[1]) * Math.PI / 180;
        var x = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(a[0] * Math.PI / 180) * Math.cos(b[0] * Math.PI / 180) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return R * 2 * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x)) * ROAD;
    }
    function mins(km, speed) {
        var m = Math.max(1, Math.round(km / speed * 60));
        return m < 60 ? m + ' min' : Math.floor(m / 60) + ' h ' + (m % 60) + ' min';
    }

    function fillOptions() {
        var fuel = $('tcFuel'), keep = fuel.value, seen = {}, brgy = {};
        fuel.innerHTML = '';
        stations.forEach(function (s) {
            (s.current_prices_list || []).forEach(function (p) {
                if (!seen[p.fuel_type_id]) { seen[p.fuel_type_id] = 1; var o = document.createElement('option'); o.value = p.fuel_type_id; o.textContent = p.fuel_type.name; fuel.appendChild(o); }
            });
            if (s.barangay && typeof s.latitude === 'number') { (brgy[s.barangay] = brgy[s.barangay] || []).push([s.latitude, s.longitude]); }
        });
        if (keep && seen[keep]) fuel.value = keep;
        var sel = $('tcBrgy'), cur = sel.value;
        sel.length = 1;
        Object.keys(brgy).sort().forEach(function (b) { var o = document.createElement('option'); o.value = b; o.textContent = b; sel.appendChild(o); });
        sel.value = cur;
        sel._centroids = brgy;
        drawMarkers();
    }

    function drawMarkers() {
        if (!map) return;
        if (!layer) layer = L.layerGroup().addTo(map);
        layer.clearLayers();
        stations.forEach(function (s) {
            if (typeof s.latitude !== 'number' || typeof s.longitude !== 'number') return;
            var prices = (s.current_prices_list || []).map(function (p) { return esc(p.fuel_type.name) + ': ' + peso(p.price); }).join('<br>') || 'No prices yet';
            L.circleMarker([s.latitude, s.longitude], { radius: 7, color: '#fff', weight: 2, fillColor: '#F58220', fillOpacity: 1 })
                .bindPopup('<strong>' + esc(s.station_name) + '</strong><br><small>' + esc(s.barangay) + '</small><hr class="my-1">' + prices + '<hr class="my-1"><a href="/stations/' + encodeURIComponent(s.id) + '">View station</a>')
                .addTo(layer);
        });
    }

    function setMode(v) {
        on = v;
        try { localStorage.setItem('ggTripOn', v ? '1' : '0'); } catch (e) {}
        $('tcInputs').style.display = v ? '' : 'none';
        $('tcResults').style.display = v ? '' : 'none';
        $('tcLeft').className = v ? 'col-lg-5' : 'col-lg-12';
        $('tcMap').style.height = v ? '220px' : '480px';
        if (!origin || !v) $('tcOriginText').textContent = v ? $('tcOriginText').getAttribute('data-default') : 'Map view only. Tap a station for its prices.';
        if (map) setTimeout(function () { map.invalidateSize(); }, 50);
        if (v) calc();
    }

    function setOrigin(lat, lng, label, fly) {
        origin = [lat, lng];
        if (originMarker) originMarker.setLatLng(origin); else originMarker = L.marker(origin, { draggable: true }).addTo(map).on('dragend', function (e) { var p = e.target.getLatLng(); setOrigin(p.lat, p.lng, 'a custom point', false); $('tcBrgy').value = ''; });
        if (fly) map.setView(origin, 14);
        $('tcOriginText').textContent = 'Starting from ' + label + '. Drag the pin to adjust.';
        calc();
    }

    function calc() {
        var ft = $('tcFuel').value, speed = parseFloat($('tcMode').value) || 30;
        var rows = $('tcRows'), best = $('tcBest');
        best.innerHTML = '';
        if (!origin) { rows.innerHTML = '<tr><td colspan="6" class="text-center text-muted-gg py-4"><i class="bi bi-geo-alt fs-4 d-block mb-1"></i>Step 2: choose where you are starting from.</td></tr>'; return; }
        var list = [];
        stations.forEach(function (s) {
            if (typeof s.latitude !== 'number' || typeof s.longitude !== 'number') return;
            var p = (s.current_prices_list || []).find(function (x) { return String(x.fuel_type_id) === String(ft); });
            list.push({ s: s, p: p, d: dist(origin, [s.latitude, s.longitude]) });
        });
        list.sort(function (a, b) { return a.d - b.d; });
        if (!list.length) { rows.innerHTML = '<tr><td colspan="6" class="text-center text-muted-gg py-4">No stations found.</td></tr>'; return; }
        var n = list[0];
        best.innerHTML = '<div class="card mb-3" style="border-color:var(--gg-accent); background:var(--gg-accent-light);"><div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">' +
            '<div><div class="small fw-bold" style="color:var(--gg-accent);"><i class="bi bi-geo-alt-fill me-1"></i>NEAREST STATION</div><div class="fs-5 fw-bold">' + esc(n.s.station_name) + '</div>' +
            '<div class="text-muted-gg small">' + esc(n.s.address) + ', ' + esc(n.s.barangay) + '</div></div>' +
            '<div class="text-end"><div class="fs-4 fw-bold">' + n.d.toFixed(1) + ' km</div><div class="text-muted-gg">about ' + mins(n.d, speed) + '</div></div></div></div>';
        rows.innerHTML = list.map(function (r, i) {
            var s = r.s, p = r.p, price = '-';
            if (p) {
                var dot = ({ enough: 'var(--gg-green)', almost_empty: 'var(--gg-amber)', no_fuel: 'var(--gg-red-status)' })[p.availability_status] || 'var(--gg-muted)';
                price = peso(p.price) + ' <span style="color:' + dot + ';" title="' + esc(p.availability_status) + '">&#9679;</span>' + (p.stale ? ' <span title="Price may be outdated" style="color:var(--gg-amber);">&#9888;</span>' : '');
            }
            return '<tr' + (i === 0 ? ' style="background:var(--gg-accent-light);"' : '') + '><td class="fw-bold">' + (i + 1) + '</td>' +
                '<td><div class="fw-semibold small">' + esc(s.station_name) + '</div><div class="text-muted-gg" style="font-size:.74rem;">' + esc(s.barangay) + '</div></td>' +
                '<td class="text-end small fw-semibold">' + r.d.toFixed(1) + ' km</td><td class="text-end small">' + mins(r.d, speed) + '</td>' +
                '<td class="text-end small">' + price + '</td>' +
                '<td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-brand" href="/stations/' + encodeURIComponent(s.id) + '">View</a> ' +
                '<a class="btn btn-sm btn-outline-brand" target="_blank" rel="noopener" title="Directions" href="https://www.google.com/maps/dir/?api=1&destination=' + s.latitude + ',' + s.longitude + '"><i class="bi bi-sign-turn-right-fill"></i></a></td></tr>';
        }).join('');
        try { localStorage.setItem('ggTripMode', $('tcMode').value); } catch (e) {}
    }

    function load() {
        return fetch(DATA_URL, { headers: { 'Accept': 'application/json' } }).then(function (r) { if (!r.ok) throw new Error(); return r.json(); }).then(function (rows) {
            stations = rows; fillOptions(); calc();
            $('tcLiveText').textContent = 'Prices updated ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }).catch(function () { $('tcLiveText').textContent = 'Could not refresh prices'; if (!stations.length) $('tcRows').innerHTML = '<tr><td colspan="6" class="text-center text-danger small py-4">Could not load station data. Please refresh the page.</td></tr>'; });
    }

    document.addEventListener('DOMContentLoaded', function () {
        map = L.map('tcMap', { scrollWheelZoom: false }).setView(CENTER, 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors', maxZoom: 19 }).addTo(map);
        map.on('click', function (e) { if (!on) return; $('tcBrgy').value = ''; setOrigin(e.latlng.lat, e.latlng.lng, 'a custom point', false); });
        try { var m = localStorage.getItem('ggTripMode'); if (m) $('tcMode').value = m; } catch (e) {}
        ['tcFuel', 'tcMode'].forEach(function (id) { $(id).addEventListener('change', calc); });
        $('tcLocate').addEventListener('click', function () {
            if (!navigator.geolocation) { $('tcOriginText').textContent = 'Location is not available on this device. Tap the map instead.'; return; }
            $('tcBrgy').value = '';
            navigator.geolocation.getCurrentPosition(function (pos) { setOrigin(pos.coords.latitude, pos.coords.longitude, 'your location', true); },
                function () { $('tcOriginText').textContent = 'Could not get your location. Tap the map or pick a barangay instead.'; }, { enableHighAccuracy: true, timeout: 10000 });
        });
        $('tcBrgy').addEventListener('change', function () {
            var pts = this._centroids && this._centroids[this.value];
            if (!pts) return;
            var lat = pts.reduce(function (a, p) { return a + p[0]; }, 0) / pts.length, lng = pts.reduce(function (a, p) { return a + p[1]; }, 0) / pts.length;
            setOrigin(lat, lng, this.value + ' (barangay center)', true);
        });
        $('tcSwitch').addEventListener('change', function () { setMode(this.checked); });
        var saved = true; try { saved = localStorage.getItem('ggTripOn') !== '0'; } catch (e) {}
        $('tcSwitch').checked = saved; setMode(saved);
        load();
        setInterval(function () { if (!document.hidden) load(); }, 60000);
    });
})();
</script>
@endpush
