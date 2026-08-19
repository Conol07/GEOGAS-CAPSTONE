@php
$sidebarItems = [
    ["route"=>"station.dashboard","label"=>"Dashboard","icon"=>"bi-speedometer2"],
    ["route"=>"station.prices.create","label"=>"Update Fuel Prices","icon"=>"bi-pencil-square"],
    ["route"=>"station.history","label"=>"Update History","icon"=>"bi-clock-history"],
];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.dashboard'])
@section('title', 'Station Dashboard')

@section('dashboard-content')
<p class="text-muted-gg small mb-1">Welcome back</p>
<h4 class="mb-1">{{ $station->station_name }}</h4>
<p class="text-muted-gg small mb-4"><i class="bi bi-geo-alt-fill me-1"></i>{{ $station->address }}, {{ $station->barangay }}</p>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-check-circle me-1"></i>Current Verified Prices</div>
            <div class="card-body">
                @if($station->latestApprovedPrice)
                    <div class="gg-price-grid mb-2">
                        <div class="gg-price-item"><div class="label">Gasoline</div><div class="value">₱{{ number_format($station->latestApprovedPrice->gasoline_price,2) }}</div></div>
                        <div class="gg-price-item"><div class="label">Diesel</div><div class="value">₱{{ number_format($station->latestApprovedPrice->diesel_price,2) }}</div></div>
                    </div>
                    <p class="text-muted-gg small mb-0">As of {{ $station->latestApprovedPrice->effective_date->format('M d, Y') }}</p>
                @else
                    <p class="text-muted-gg mb-0">No verified price yet.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-hourglass-split me-1"></i>Pending Submissions</div>
            <div class="card-body">
                @forelse($station->pendingPrices as $p)
                    <p class="mb-2 small">Gasoline ₱{{ number_format($p->gasoline_price,2) }} / Diesel ₱{{ number_format($p->diesel_price,2) }} <span class="text-muted-gg">— submitted {{ $p->created_at->diffForHumans() }}</span></p>
                @empty
                    <p class="text-muted-gg small mb-0">No pending submissions.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<a href="{{ route('station.prices.create') }}" class="btn btn-brand btn-lg mb-4"><i class="bi bi-pencil-square me-1"></i>Update Fuel Prices</a>

<h6 class="mb-2">Recent Updates</h6>
@if($recentUpdates->isEmpty())
    <x-empty-state icon="bi-clock-history" title="No updates yet" message="Your submitted price updates will appear here." />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Date</th><th>Gasoline</th><th>Diesel</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($recentUpdates as $u)
            <tr>
                <td>{{ $u->effective_date->format('M d, Y') }}</td>
                <td>₱{{ number_format($u->gasoline_price,2) }}</td>
                <td>₱{{ number_format($u->diesel_price,2) }}</td>
                <td><x-status-badge :status="$u->status" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
