@extends('layouts.app')
@section('title', 'Home')

@section('content')
@php
    $lowestGasoline = $stations->filter(fn($s) => $s->latestApprovedPrice)
        ->min(fn($s) => $s->latestApprovedPrice->gasoline_price);
@endphp

<div class="gg-hero mb-4">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <span class="gg-hero-eyebrow"><i class="bi bi-broadcast"></i> Fuel Price Monitoring System</span>
            <h1>Find. Compare. Locate.</h1>
            <p class="text-muted-gg" style="font-size:1.05rem; max-width:520px;">
                Verified fuel prices across participating gasoline stations in Manolo Fortich, Bukidnon.
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
                        <div class="text-muted-gg small">Barangay Tankulan coverage area</div>
                    </div>
                </div>
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="gg-stat-value">{{ $stats['total_stations'] }}</div>
                        <div class="gg-stat-label">Participating Stations</div>
                    </div>
                    <div class="col-6">
                        <div class="gg-stat-value">
                            {{ $lowestGasoline ? '₱'.number_format($lowestGasoline,2) : '—' }}
                        </div>
                        <div class="gg-stat-label">Lowest Verified Gasoline</div>
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
        <x-stat-card icon="bi-check2-circle" label="Verified Updates Today" :value="$stats['updates_today']" desc="Approved by the administrator" />
    </div>
    <div class="col-6 col-md-3">
        <x-stat-card icon="bi-droplet-fill" label="Fuel Types Tracked" value="4" desc="Gasoline, Diesel, Premium, Regular" />
    </div>
    <div class="col-6 col-md-3">
        <x-stat-card icon="bi-map-fill" label="Coverage Area" value="Tankulan" desc="Manolo Fortich, Bukidnon" />
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Participating Stations</h5>
    <a href="{{ route('stations.index') }}" class="small">View all →</a>
</div>

<div class="row g-3">
    @forelse($stations as $station)
        <div class="col-md-6 col-lg-4">
            <x-station-card :station="$station" :isLowest="$station->latestApprovedPrice && $lowestGasoline && (float) $station->latestApprovedPrice->gasoline_price === (float) $lowestGasoline" />
        </div>
    @empty
        <div class="col-12">
            <x-empty-state icon="bi-shop" title="No stations available yet" />
        </div>
    @endforelse
</div>
@endsection
