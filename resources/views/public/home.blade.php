@extends('layouts.app')
@section('title', 'Home')

@section('content')
{{-- HERO --}}
<div class="gg-dash-hero mb-4">
    <div class="row align-items-center g-4">
        <div class="col-lg-9">
            <h1>Find Fuel<br><span class="accent">Near You</span></h1>
            <p class="mb-4">Up-to-date fuel prices. Nearby stations. A more informed Manolo Fortich.</p>
        </div>
    </div>
</div>

{{-- PRICE OVERVIEW --}}
<div id="liveOverview" data-live="60">
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
                            <div class="fw-bold" style="color:var(--gg-green);">₱{{ number_format($row['lowest']->price,2) }}</div>
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
                            <div class="fw-bold" style="color:var(--gg-red-status);">₱{{ number_format($row['highest']->price,2) }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
</div>

{{-- GIS CONSOLE: markers / price heatmap / combined --}}
<div class="mb-4" id="gisSection">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0"><i class="bi bi-map-fill me-1" style="color:var(--gg-accent);"></i>Fuel Price Map &mdash; Manolo Fortich</h6>
        <span class="small text-muted-gg">{{ $stats['total_stations'] }} active station{{ $stats['total_stations'] == 1 ? '' : 's' }}</span>
    </div>
    <x-gis-console instance-id="gis" :auto-init="true" height="clamp(320px, 60vh, 460px)" />
</div>

{{-- NEARBY + UPDATES --}}
<div class="row g-3">
    <div class="col-lg-7">
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

    <div class="col-lg-5">
        <div class="gg-location-card mb-3" id="liveUpdates" data-live="60">
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
        <div class="col-md-6 col-lg-4">
            <x-news-card :news="$n" />
        </div>
    @endforeach
</div>
@endif

@push('scripts')
<script>
(function () {
    var AVAIL = {
        enough: { label: 'Has Enough Gasoline', color: '#15803D' },
        almost_empty: { label: 'Almost Empty', color: '#B45309' },
        no_fuel: { label: 'No Gasoline', color: '#B91C1C' },
        unknown: { label: 'No Data Yet', color: '#6B7280' }
    };
    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    var NEAREST_URL = {!! json_encode(route('nearest')) !!};
    var list = document.getElementById('nearbyList');

    function focusStation(id) {
        if (typeof window.gisFocusStation_gis === 'function') window.gisFocusStation_gis(id);
        var sec = document.getElementById('gisSection');
        if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    list.addEventListener('click', function (e) {
        var row = e.target.closest('[data-station-id]');
        if (row) focusStation(parseInt(row.dataset.stationId, 10));
    });

    function renderNearby(stations) {
        document.getElementById('nearbyHint').textContent = 'Sorted by distance from your current location.';
        list.innerHTML = '';
        stations.forEach(function (s) {
            var prices = (s.prices || []).slice(0, 2).map(function (p) {
                var m = AVAIL[p.availability_status] || AVAIL.unknown;
                return '<div class="gg-dash-price-row"><span>' + esc(p.fuel_type) + '</span><span>&#8369;' + Number(p.price).toFixed(2) +
                    ' <span style="color:' + m.color + ';" title="' + esc(m.label) + '">&#9679;</span></span></div>';
            }).join('');
            var logo = s.logo_url
                ? '<img src="' + esc(s.logo_url) + '" alt="" style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:1px solid var(--gg-border);flex-shrink:0;">'
                : '<div style="width:40px;height:40px;border-radius:10px;background:var(--gg-accent-light);color:#F58220;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-fuel-pump-fill"></i></div>';
            var km = Number(s.distance_km);
            var dist = km < 1 ? Math.round(km * 1000) + ' m' : km.toFixed(1) + ' km';
            var row = document.createElement('div');
            row.className = 'gg-dash-station-row';
            row.dataset.stationId = s.id;
            row.innerHTML = logo + '<div class="flex-grow-1"><div class="fw-semibold small">' + esc(s.station_name) + '</div>' +
                '<div class="text-muted-gg" style="font-size:.72rem;"><i class="bi bi-geo-alt-fill"></i> ' + dist + ' away &middot; ' + esc(s.barangay) + '</div>' +
                prices + '<a href="/stations/' + encodeURIComponent(s.id) + '" class="small" onclick="event.stopPropagation()">View Details</a></div>';
            list.appendChild(row);
        });
    }

    var btn = document.getElementById('enableLocationBtn');
    if (btn) btn.addEventListener('click', function () {
        if (!navigator.geolocation) { alert('Geolocation is not supported by your browser. You can still browse everything else.'); return; }
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Locating...';
        navigator.geolocation.getCurrentPosition(function (pos) {
            var lat = pos.coords.latitude, lng = pos.coords.longitude;
            if (typeof window.gisSetUserLocation_gis === 'function') window.gisSetUserLocation_gis(lat, lng);
            fetch(NEAREST_URL + '?lat=' + encodeURIComponent(lat) + '&lng=' + encodeURIComponent(lng), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(renderNearby)
                .catch(function () {})
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Location Active';
                });
        }, function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-geo-alt-fill me-1"></i>Activate My Location';
            alert('Unable to get your location. You can still use GeoGas ManFort normally without it.');
        });
    });
})();
</script>
@endpush
@endsection
