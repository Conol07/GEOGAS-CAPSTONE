@extends('layouts.app')
@section('title', $station->station_name)

@section('content')
<nav class="small text-muted-gg mb-2"><a href="{{ route('stations.index') }}">Stations</a> / {{ $station->station_name }}</nav>
<h4 class="mb-1">{{ $station->station_name }}</h4>
<p class="text-muted-gg mb-4"><i class="bi bi-geo-alt-fill me-1"></i>{{ $station->address }}, {{ $station->barangay }}, {{ $station->municipality }}, {{ $station->province }}</p>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-check-circle me-1"></i>Current Verified Prices</div>
            <div class="card-body">
                @if($station->latestApprovedPrice)
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="gg-price-item">
                                <div class="label">Gasoline</div>
                                <div class="value">₱{{ number_format($station->latestApprovedPrice->gasoline_price, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="gg-price-item">
                                <div class="label">Diesel</div>
                                <div class="value">₱{{ number_format($station->latestApprovedPrice->diesel_price, 2) }}</div>
                            </div>
                        </div>
                        @if($station->latestApprovedPrice->premium_price)
                        <div class="col-6">
                            <div class="gg-price-item">
                                <div class="label">Premium</div>
                                <div class="value">₱{{ number_format($station->latestApprovedPrice->premium_price, 2) }}</div>
                            </div>
                        </div>
                        @endif
                        @if($station->latestApprovedPrice->regular_price)
                        <div class="col-6">
                            <div class="gg-price-item">
                                <div class="label">Regular</div>
                                <div class="value">₱{{ number_format($station->latestApprovedPrice->regular_price, 2) }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <p class="text-muted-gg small mt-3 mb-0"><i class="bi bi-clock-history me-1"></i>Last verified: {{ $station->latestApprovedPrice->effective_date->format('F d, Y') }}</p>
                @else
                    <p class="text-muted-gg mb-0">No verified price yet.</p>
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
            <div class="card-header"><i class="bi bi-clock-history me-1"></i>Recent Price History (Verified)</div>
            <ul class="list-group list-group-flush">
                @forelse($station->fuelPrices as $price)
                    <li class="list-group-item small d-flex justify-content-between">
                        <span>{{ $price->effective_date->format('M d, Y') }}</span>
                        <span>Gasoline ₱{{ number_format($price->gasoline_price,2) }} · Diesel ₱{{ number_format($price->diesel_price,2) }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted-gg small">No history yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-map me-1"></i>Location</div>
            <div id="stationMap" style="height:400px;"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const map = L.map('stationMap').setView([{{ $station->latitude }}, {{ $station->longitude }}], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
    const stationIcon = L.divIcon({
        className: '',
        html: '<div class="gg-marker-pin selected"><i class="bi bi-fuel-pump-fill"></i></div>',
        iconSize: [28, 28],
        iconAnchor: [14, 28],
        popupAnchor: [0, -26],
    });
    L.marker([{{ $station->latitude }}, {{ $station->longitude }}], { icon: stationIcon }).addTo(map).bindPopup(@json($station->station_name));
</script>
@endpush
@endsection
