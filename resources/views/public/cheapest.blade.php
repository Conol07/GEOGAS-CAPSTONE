@extends('layouts.app')
@section('title', 'Cheapest Fuel Finder')

@section('content')
<h4 class="mb-1">Cheapest Fuel Finder</h4>
<p class="text-muted-gg mb-4">Select a fuel type and area to find the cheapest available station. Distance is calculated automatically if you allow location access.</p>

<form method="GET" class="row g-2 mb-4" id="cheapestFilterForm">
    <div class="col-md-3">
        <select name="fuel_type_id" class="form-select" onchange="this.form.submit()">
            @foreach($fuelTypes as $ft)
                <option value="{{ $ft->id }}" @selected($fuelTypeId==$ft->id)>{{ $ft->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="barangay" class="form-select" onchange="this.form.submit()">
            <option value="">All Areas</option>
            @foreach($barangays as $b)
                <option value="{{ $b }}" @selected($barangay==$b)>{{ $b }}</option>
            @endforeach
        </select>
    </div>
</form>

@if($prices->isEmpty())
    <x-empty-state icon="bi-trophy" title="No available stations found" message="All stations may currently be out of this fuel type in this area." />
@else
    @php $cheapest = $prices->first(); @endphp
    <div class="card mb-4" style="border-color:var(--gg-accent); background:var(--gg-accent-light);">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <x-station-logo :station="$cheapest->station" :size="52" />
                <div>
                    <div class="small fw-bold" style="color:var(--gg-accent);">CHEAPEST AVAILABLE FUEL</div>
                    <h5 class="mb-0">{{ $cheapest->station->station_name }} — ₱{{ number_format($cheapest->price,2) }}</h5>
                    <div class="text-muted-gg small">
                        {{ $cheapest->station->address }}, {{ $cheapest->station->barangay }}
                        &middot; <span class="distance-cell" data-lat="{{ $cheapest->station->latitude }}" data-lng="{{ $cheapest->station->longitude }}">Enable location to calculate distance.</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('stations.show', $cheapest->station) }}" class="btn btn-brand">View Station</a>
        </div>
    </div>

    <div class="gg-table gg-table-responsive">
        <table class="table">
            <thead><tr><th>Rank</th><th>Station</th><th>Area</th><th class="text-end">Price</th><th>Availability</th><th>Distance</th><th class="no-print">Action</th></tr></thead>
            <tbody>
            @foreach($prices as $p)
                <tr>
                    <td>#{{ $loop->iteration }}</td>
                    <td class="fw-semibold">
                        <div class="d-flex align-items-center gap-2">
                            <x-station-logo :station="$p->station" :size="28" />
                            {{ $p->station->station_name }}
                        </div>
                    </td>
                    <td class="text-muted-gg small">{{ $p->station->barangay }}</td>
                    <td class="text-end">₱{{ number_format($p->price,2) }}</td>
                    <td><x-availability-badge :status="$p->availability_status" /></td>
                    <td class="text-muted-gg small distance-cell" data-lat="{{ $p->station->latitude }}" data-lng="{{ $p->station->longitude }}">Enable location to calculate distance.</td>
                    <td><a href="{{ route('stations.show', $p->station) }}" class="btn btn-sm btn-outline-brand">View</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif

@push('scripts')
<script>
    // Distance is automatic — no button. If the browser already has (or grants)
    // location permission, every row's distance fills in on its own; if not,
    // the page still works normally and just shows the fallback message.
    function formatDistance(km) {
        return km < 1 ? `${Math.round(km * 1000)} m away` : `${km.toFixed(1)} km away`;
    }

    function haversineKm(lat1, lng1, lat2, lng2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
        return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (pos) {
            document.querySelectorAll('.distance-cell').forEach(cell => {
                const lat = parseFloat(cell.dataset.lat), lng = parseFloat(cell.dataset.lng);
                if (isNaN(lat) || isNaN(lng)) return;
                cell.textContent = formatDistance(haversineKm(pos.coords.latitude, pos.coords.longitude, lat, lng));
            });
        }, function () {
            // Permission denied or unavailable — leave the fallback message as-is.
        });
    }
</script>
@endpush
@endsection
