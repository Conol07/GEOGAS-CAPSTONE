@props(['station'])
@php
    $prices = $station->relationLoaded('current') ? $station->current : $station->currentPrices();
    $availability = $station->overallAvailability();
@endphp
<div class="gg-station-card">
    <div class="body">
        <div class="d-flex align-items-start gap-2 mb-1">
            <x-station-logo :station="$station" :size="40" />
            <div class="flex-grow-1">
                <h5 class="mb-0">{{ $station->station_name }}</h5>
                <div class="gg-station-location mb-0">
                    <i class="bi bi-geo-alt-fill mt-1"></i>
                    <span>{{ $station->address }}, {{ $station->barangay }}</span>
                </div>
            </div>
        </div>

        <div class="mb-2 mt-2"><x-availability-badge :status="$availability" /></div>

        @if($prices->isNotEmpty())
            <div class="gg-price-grid flex-wrap">
                @foreach($prices as $p)
                    <div class="gg-price-item">
                        <div class="label">{{ $p->fuelType->name }}</div>
                        @if($p->fuelType->specification)
                            <div class="text-muted-gg" style="font-size:.65rem;">{{ $p->fuelType->specification }}</div>
                        @endif
                        <div class="value" style="font-size:1.1rem;">₱{{ number_format($p->price, 2) }}</div>
                    </div>
                @endforeach
            </div>
            <div class="updated"><i class="bi bi-clock-history me-1"></i>Last updated: {{ $prices->max('created_at')->diffForHumans() }}</div>
        @else
            <div class="updated text-muted-gg"><i class="bi bi-info-circle me-1"></i>No price data yet.</div>
        @endif
    </div>
    <a href="{{ route('stations.show', $station) }}" class="footer-link d-flex justify-content-between align-items-center text-decoration-none">
        View Details <i class="bi bi-arrow-right"></i>
    </a>
</div>
