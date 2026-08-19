@props(['station', 'isLowest' => false])
<div class="gg-station-card">
    <div class="body">
        <h5>{{ $station->station_name }}</h5>
        <div class="gg-station-location">
            <i class="bi bi-geo-alt-fill mt-1"></i>
            <span>{{ $station->address }}, {{ $station->barangay }}</span>
        </div>

        @if($station->latestApprovedPrice)
            <div class="gg-price-grid">
                <div class="gg-price-item">
                    <div class="label">Gasoline</div>
                    <div class="value">₱{{ number_format($station->latestApprovedPrice->gasoline_price, 2) }}
                        @if($isLowest)<span class="gg-lowest-badge">LOWEST</span>@endif
                    </div>
                </div>
                <div class="gg-price-item">
                    <div class="label">Diesel</div>
                    <div class="value">₱{{ number_format($station->latestApprovedPrice->diesel_price, 2) }}</div>
                </div>
            </div>
            <div class="updated"><i class="bi bi-clock-history me-1"></i>Last verified: {{ $station->latestApprovedPrice->effective_date->format('M d, Y') }}</div>
        @else
            <div class="updated text-muted-gg"><i class="bi bi-info-circle me-1"></i>No verified price yet.</div>
        @endif
    </div>
    <a href="{{ route('stations.show', $station) }}" class="footer-link d-flex justify-content-between align-items-center text-decoration-none">
        View Details <i class="bi bi-arrow-right"></i>
    </a>
</div>
