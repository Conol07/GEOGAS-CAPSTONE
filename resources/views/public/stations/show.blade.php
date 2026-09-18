@extends('layouts.app')
@section('title', $station->station_name)

@section('content')
<nav class="small text-muted-gg mb-2"><a href="{{ route('stations.index') }}">Stations</a> / {{ $station->station_name }}</nav>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-1">
    <div class="d-flex align-items-center gap-2">
        <x-station-logo :station="$station" :size="48" />
        <h4 class="mb-0">{{ $station->station_name }}</h4>
    </div>
    <x-availability-badge :status="$station->overallAvailability()" />
</div>
<p class="text-muted-gg mb-4"><i class="bi bi-geo-alt-fill me-1"></i>{{ $station->address }}, {{ $station->barangay }}, {{ $station->municipality }}, {{ $station->province }}</p>

<div class="row g-3">
    <div class="col-lg-6">
        @if($station->photo_path)
            <div class="card mb-3">
                <img src="{{ $station->photoUrl() }}" alt="{{ $station->station_name }}" class="w-100" style="max-height:260px; object-fit:cover; border-radius:14px 14px 0 0;">
            </div>
        @endif

        @if($station->description)
            <div class="card mb-3">
                <div class="card-body">
                    <p class="mb-0 small">{{ $station->description }}</p>
                </div>
            </div>
        @endif

        @if($recentHistory->isNotEmpty())
            @php $latest = $recentHistory->first(); @endphp
            <div class="card mb-3" style="border-color:var(--gg-accent); background:var(--gg-accent-light);">
                <div class="card-body py-3">
                    <div class="small fw-bold" style="color:var(--gg-accent);">LATEST UPDATE</div>
                    <div class="fw-semibold">{{ $latest->fuelType->name }} price updated to ₱{{ number_format($latest->price,2) }}</div>
                    <div class="text-muted-gg small">{{ $latest->created_at->diffForHumans() }}</div>
                    <div class="mt-1"><x-availability-badge :status="$latest->availability_status" /></div>
                </div>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-cash-coin me-1"></i>Current Verified Prices</div>
            <div class="card-body">
                @if($currentPrices->isNotEmpty())
                    <div class="row g-3">
                        @foreach($currentPrices as $p)
                            <div class="col-6">
                                <div class="gg-price-item @if($highlightFuelTypeId && $p->fuel_type_id == $highlightFuelTypeId) gg-price-highlighted @endif">
                                    <div class="label">{{ $p->fuelType->name }}</div>
                                    @if($p->fuelType->specification)
                                        <div class="text-muted-gg" style="font-size:.68rem;">{{ $p->fuelType->specification }}</div>
                                    @endif
                                    <div class="value">₱{{ number_format($p->price, 2) }}</div>
                                    <div class="mt-1"><x-availability-badge :status="$p->availability_status" /></div>
                                    <div class="mt-1"><x-price-change :price="$p" /></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-muted-gg small mt-3 mb-0">
                        <i class="bi bi-clock-history me-1"></i>Last updated: {{ $currentPrices->max('created_at')->diffForHumans() }}
                        @if($currentPrices->max('created_at')->lt(now()->subHours(72)))
                            <br><span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Price information may be outdated.</span>
                        @endif
                    </p>
                @else
                    <p class="text-muted-gg mb-0">No price data yet.</p>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-info-circle me-1"></i>Station Information</div>
            <div class="card-body">
                <p class="mb-1 small"><i class="bi bi-telephone-fill me-2 text-muted-gg"></i>{{ $station->contact_number ?? 'Not provided' }}</p>
                <p class="mb-0 small"><i class="bi bi-toggle-on me-2 text-muted-gg"></i><x-status-badge :status="$station->status" /></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-tools me-1"></i>Services &amp; Amenities</div>
            <div class="card-body">
                @if($services->isEmpty())
                    <p class="text-muted-gg small mb-0">No additional services listed.</p>
                @else
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($services as $s)
                            <span class="badge" style="background:var(--gg-accent-light); color:var(--gg-dark); font-weight:600;">
                                <i class="bi bi-check-circle-fill me-1"></i>{{ $s->label }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-map me-1"></i>Location</div>
            <div id="stationMap" style="height:400px;"></div>
        </div>
    </div>
</div>

{{-- Fuel price analytics — placed below the map, using this station's actual price history --}}
<h5 class="mt-4 mb-1"><i class="bi bi-graph-up me-1"></i>Fuel Price Analytics</h5>
<p class="text-muted-gg small mb-3">Historical prices for {{ $station->station_name }}. 🟢 decreased &middot; 🔴 increased &middot; ⚪ no change.</p>

<div class="row g-3 mb-3">
    @forelse($fuelTypes as $ft)
        @php $rows = $historyByFuelType->get($ft->id, collect()); @endphp
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">{{ $ft->name }} Price History</div>
                <div class="card-body">
                    @if($rows->count() < 2)
                        <p class="text-muted-gg small mb-0">Not enough recorded updates yet to chart a trend.</p>
                    @else
                        <canvas id="chart{{ $ft->id }}" height="140"></canvas>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><x-empty-state icon="bi-graph-up" title="No fuel types configured" /></div>
    @endforelse
</div>

<div class="card mb-4">
    <div class="card-header"><i class="bi bi-clock-history me-1"></i>Recent Price Updates</div>
    <ul class="list-group list-group-flush">
        @forelse($recentHistory as $h)
            <li class="list-group-item small d-flex justify-content-between align-items-center">
                <span>{{ $h->created_at->format('M d, Y g:i A') }} — {{ $h->fuelType->name }}</span>
                <span>₱{{ number_format($h->price,2) }} <x-price-change :price="$h" /></span>
            </li>
        @empty
            <li class="list-group-item text-muted-gg small">No history yet.</li>
        @endforelse
    </ul>
</div>

<div class="mt-3">
    <a href="{{ route('complaints.create') }}?station_id={{ $station->id }}" class="btn btn-outline-brand btn-sm">
        <i class="bi bi-flag-fill me-1"></i>Report an issue with this station
    </a>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const map = L.map('stationMap').setView([{{ $station->latitude }}, {{ $station->longitude }}], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
    const stationIcon = L.divIcon({
        className: '',
        html: '<div class="gg-marker-pin selected"><i class="bi bi-fuel-pump-fill"></i></div>',
        iconSize: [28, 28], iconAnchor: [14, 28], popupAnchor: [0, -26],
    });
    L.marker([{{ $station->latitude }}, {{ $station->longitude }}], { icon: stationIcon }).addTo(map).bindPopup(@json($station->station_name));

    const orange = '#F58220';
    @foreach($fuelTypes as $ft)
        @php $rows = $historyByFuelType->get($ft->id, collect()); @endphp
        @if($rows->count() >= 2)
        new Chart(document.getElementById('chart{{ $ft->id }}'), {
            type: 'line',
            data: {
                labels: @json($rows->pluck('created_at')->map(fn($d) => $d->format('M d'))),
                datasets: [{
                    label: '{{ $ft->name }} Price (₱)',
                    data: @json($rows->pluck('price')->map(fn($p) => (float) $p)),
                    borderColor: orange, backgroundColor: 'rgba(245,130,32,0.15)', fill: true, tension: 0.3,
                }]
            },
            options: { plugins: { legend: { display: false } } }
        });
        @endif
    @endforeach
</script>
@endpush
@endsection
