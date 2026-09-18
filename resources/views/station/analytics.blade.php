@php
$sidebarItems = \App\Support\Nav::stationSidebar(auth()->user());
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.analytics.index'])
@section('title', 'Analytics')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">Analytics — {{ $station->station_name }}</h4>
        <p class="text-muted-gg small mb-0">Historical fuel price trends for your station.</p>
    </div>
    @if(auth()->user()->hasStationPermission('print_reports'))
        <button class="btn btn-outline-brand btn-sm no-print" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print Analytics</button>
    @endif
</div>

<div id="printHeader" class="d-none d-print-block mb-3">
    <h5 class="mb-0">GeoGas ManFort</h5>
    <div class="fw-bold">{{ $station->station_name }}</div>
    <div class="small text-muted-gg">Reporting Period: All available records &middot; Generated: {{ now()->format('F d, Y g:i A') }}</div>
    <hr>
</div>

<div class="row g-3 mb-4">
    @foreach($currentPrices as $p)
        <div class="col-6 col-md-3">
            <div class="gg-stat-card">
                <div class="gg-stat-label">{{ $p->fuelType->name }}</div>
                <div class="gg-stat-value" style="font-size:1.4rem;">₱{{ number_format($p->price,2) }}</div>
                <x-availability-badge :status="$p->availability_status" />
            </div>
        </div>
    @endforeach
</div>

@forelse($fuelTypes as $ft)
    @php $rows = $historyByFuelType->get($ft->id, collect()); @endphp
    <div class="card mb-4">
        <div class="card-header">{{ $ft->name }} Price History</div>
        <div class="card-body">
            @if($rows->count() < 2)
                <p class="text-muted-gg small mb-0">Not enough recorded updates yet to chart a trend for {{ $ft->name }}.</p>
            @else
                <canvas id="chart{{ $ft->id }}" height="90"></canvas>
            @endif
        </div>
    </div>
@empty
    <x-empty-state icon="bi-graph-up" title="No fuel types configured" />
@endforelse

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const orange = '#F58220';
    @foreach($fuelTypes as $ft)
        @php $rows = $historyByFuelType->get($ft->id, collect()); @endphp
        @if($rows->count() >= 2)
        new Chart(document.getElementById('chart{{ $ft->id }}'), {
            type: 'line',
            data: {
                labels: @json($rows->pluck('created_at')->map(fn($d) => $d->format('M d, g:i A'))),
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
