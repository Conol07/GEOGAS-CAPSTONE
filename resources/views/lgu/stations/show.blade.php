@php
$sidebarItems = \App\Support\Nav::lguSidebar();
$groups = ['enough' => '#15803D', 'almost_empty' => '#B45309', 'no_fuel' => '#B91C1C'];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.stations.index'])
@section('title', $station->station_name)

@section('dashboard-content')
<nav class="small text-muted-gg mb-2"><a href="{{ route('lgu.stations.index') }}"><i class="bi bi-arrow-left"></i> Back to Stations</a> / {{ $station->station_name }}</nav>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
    <div class="d-flex align-items-center gap-3">
        <x-station-logo :station="$station" :size="56" />
        <div>
            <h4 class="mb-1">{{ $station->station_name }}</h4>
            <div class="d-flex gap-2 flex-wrap">
                <x-status-badge :status="$station->status" />
                <x-availability-badge :status="$station->overallAvailability()" />
            </div>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('lgu.stations.edit', $station) }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('lgu.reports.station-record', ['station_id' => $station->id]) }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-printer"></i> Station Record</a>
        <a href="{{ route('lgu.complaints.index', ['station_id' => $station->id]) }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-flag"></i> All Complaints ({{ $complaintTotal }})</a>
        <form method="POST" action="{{ route('lgu.stations.toggle', $station) }}" onsubmit="return confirm('{{ $station->status === 'active' ? 'Deactivate' : 'Activate' }} this station?');">
            @csrf @method('PATCH')
            <button class="btn btn-sm {{ $station->status === 'active' ? 'btn-outline-danger' : 'btn-brand' }}">
                <i class="bi bi-power"></i> {{ $station->status === 'active' ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">Station Information</div>
            <div class="card-body small">
                <p class="mb-1"><strong>Address:</strong> {{ $station->address }}, {{ $station->barangay }}, {{ $station->municipality }}, {{ $station->province }}</p>
                <p class="mb-1"><strong>Owner / Company:</strong> {{ $station->company_owner ?: '—' }}</p>
                <p class="mb-1"><strong>Contact:</strong> {{ $station->contact_number ?: '—' }} @if($station->email) &middot; {{ $station->email }} @endif</p>
                <p class="mb-1"><strong>Coordinates:</strong> {{ $station->latitude }}, {{ $station->longitude }}</p>
                @if($station->description)<p class="mb-1"><strong>Description:</strong> {{ $station->description }}</p>@endif
                <p class="mb-0"><strong>Services:</strong>
                    @forelse($station->services->where('available', true) as $svc)
                        <span class="badge text-bg-light border">{{ $svc->label }}</span>
                    @empty
                        —
                    @endforelse
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">Location</div>
            <div class="card-body p-0"><div id="stationMiniMap" style="height:220px;"></div></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">Manager Account</div>
            <div class="card-body">
                @forelse($managers as $p)
                    @include('lgu.stations._person', ['person' => $p])
                @empty
                    <p class="text-muted-gg small mb-0">No manager account linked.</p>
                @endforelse
                <p class="text-muted-gg mb-0 mt-2" style="font-size:.72rem;"><i class="bi bi-shield-lock"></i> Passwords and credentials are never shown.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">Authorized Staff ({{ $staff->count() }})</div>
            <div class="card-body">
                @forelse($staff as $p)
                    @include('lgu.stations._person', ['person' => $p])
                @empty
                    <p class="text-muted-gg small mb-0">No staff accounts.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">Current Fuel Prices</div>
            <div class="card-body">
                @forelse($currentPrices as $p)
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <div>
                            <div class="fw-semibold small">{{ $p->fuelType->name }}</div>
                            <div class="text-muted-gg" style="font-size:.72rem;">Updated {{ $p->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">₱{{ number_format($p->price, 2) }}</div>
                            <x-availability-badge :status="$p->availability_status" />
                        </div>
                    </div>
                @empty
                    <p class="text-muted-gg small mb-0">No prices reported yet.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">Price Update History (latest 20)</div>
            <div class="gg-table gg-table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Fuel</th><th>Price</th><th>Availability</th><th>By</th><th>When</th></tr></thead>
                    <tbody>
                    @forelse($priceHistory as $h)
                        <tr>
                            <td>{{ $h->fuelType->name }}</td>
                            <td>₱{{ number_format($h->price, 2) }}</td>
                            <td><x-availability-badge :status="$h->availability_status" /></td>
                            <td class="small">{{ optional($h->updater)->name ?? '—' }}</td>
                            <td class="small text-muted-gg">{{ $h->created_at->format('M d, g:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted-gg small">No history yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">Recent Complaints</div>
            <div class="card-body">
                @forelse($complaints as $c)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <div class="fw-semibold small">{{ $c->subject }}</div>
                            <div class="text-muted-gg" style="font-size:.72rem;">{{ $c->reference_no }} &middot; {{ $c->created_at->format('M d, Y') }}</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <x-status-badge :status="$c->status" />
                            <a href="{{ route('lgu.complaints.show', $c) }}" class="btn btn-sm btn-outline-brand">Review</a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted-gg small mb-0">No complaints for this station.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">Station Activity</div>
            <div class="card-body">
                @forelse($activity as $a)
                    <div class="py-1 border-bottom small">
                        <div class="fw-semibold">{{ $a->action }}</div>
                        <div class="text-muted-gg" style="font-size:.72rem;">{{ optional($a->user)->name ?? 'System' }} &middot; {{ $a->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <p class="text-muted-gg small mb-0">No recorded activity.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var lat = {{ (float) $station->latitude }}, lng = {{ (float) $station->longitude }};
    var map = L.map('stationMiniMap', { scrollWheelZoom: false }).setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
    L.marker([lat, lng]).addTo(map);
})();
</script>
@endpush
@endsection
