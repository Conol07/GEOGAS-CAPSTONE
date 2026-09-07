@extends('layouts.app')
@section('title', 'Home')

@section('content')

<div class="gg-hero mb-4">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <span class="gg-hero-eyebrow"><i class="bi bi-broadcast"></i> Fuel Price Monitoring System</span>
            <h1>Find. Compare. Locate.</h1>
            <p class="text-muted-gg" style="font-size:1.05rem; max-width:560px;">
                View the latest manager-updated fuel prices and real-time availability
                from participating gasoline stations in Manolo Fortich, Bukidnon.
            </p>
            <div class="d-flex gap-2 mt-4 flex-wrap">
                <a href="{{ route('stations.index') }}" class="btn btn-brand btn-lg"><i class="bi bi-shop me-1"></i>View Stations</a>
                <a href="{{ route('compare') }}" class="btn btn-outline-brand btn-lg"><i class="bi bi-bar-chart-line me-1"></i>Compare Prices</a>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="gg-hero-card">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="gg-stat-icon mb-0"><i class="bi bi-geo-alt-fill"></i></div>
                    <div>
                        <div class="fw-bold">Manolo Fortich, Bukidnon</div>
                        <div class="text-muted-gg small">Municipality-wide coverage</div>
                    </div>
                </div>
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="gg-stat-value">{{ $stats['total_stations'] }}</div>
                        <div class="gg-stat-label">Participating Stations</div>
                    </div>
                    <div class="col-6">
                        <div class="gg-stat-value">{{ $stats['fuel_types'] }}</div>
                        <div class="gg-stat-label">Fuel Types Tracked</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <x-stat-card icon="bi-shop" label="Participating Stations" :value="$stats['total_stations']" desc="Registered in the system" />
    </div>
    <div class="col-6 col-md-3">
        <x-stat-card icon="bi-clock-history" label="Updates Today" :value="$stats['updates_today']" desc="Prices/availability updated" />
    </div>
    <div class="col-6 col-md-3">
        <x-stat-card icon="bi-droplet-fill" label="Fuel Types Tracked" :value="$stats['fuel_types']" desc="Regular, Premium, Diesel" />
    </div>
    <div class="col-6 col-md-3">
        <div class="gg-stat-card">
            <div class="gg-stat-icon"><i class="bi bi-flag-fill"></i></div>
            <div class="gg-stat-value" style="font-size:1.1rem;">Report an Issue</div>
            <div class="gg-stat-label">No account needed</div>
            <a href="{{ route('complaints.create') }}" class="small">Send a complaint →</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <a href="{{ route('areas') }}" class="card text-decoration-none h-100">
            <div class="card-body p-4">
                <div class="gg-stat-icon mb-3"><i class="bi bi-map"></i></div>
                <h6 class="mb-1">Compare Prices by Area</h6>
                <p class="text-muted-gg small mb-0">See average, lowest, and highest prices per barangay.</p>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a href="{{ route('cheapest') }}" class="card text-decoration-none h-100">
            <div class="card-body p-4">
                <div class="gg-stat-icon mb-3"><i class="bi bi-trophy-fill"></i></div>
                <h6 class="mb-1">Find the Cheapest Fuel</h6>
                <p class="text-muted-gg small mb-0">Pick a fuel type, see the cheapest available station.</p>
            </div>
        </a>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Participating Stations</h5>
    <a href="{{ route('stations.index') }}" class="small">View all →</a>
</div>

<div class="row g-3">
    @forelse($stations as $station)
        <div class="col-md-6 col-lg-4">
            <x-station-card :station="$station" />
        </div>
    @empty
        <div class="col-12"><x-empty-state icon="bi-shop" title="No stations available yet" /></div>
    @endforelse
</div>
@endsection
