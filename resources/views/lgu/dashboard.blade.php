@php
$sidebarItems = [
    ["route"=>"lgu.dashboard","label"=>"Dashboard","icon"=>"bi-speedometer2"],
    ["route"=>"lgu.stations.index","label"=>"Stations","icon"=>"bi-shop"],
    ["route"=>"lgu.users.index","label"=>"Users","icon"=>"bi-people-fill"],
    ["route"=>"lgu.complaints.index","label"=>"Complaints","icon"=>"bi-flag-fill"],
    ["route"=>"lgu.analytics.index","label"=>"Analytics","icon"=>"bi-graph-up"],
    ["route"=>"lgu.reports.index","label"=>"Reports","icon"=>"bi-file-earmark-text-fill"],
];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.dashboard'])
@section('title', 'LGU Dashboard')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">LGU Dashboard</h4>
        <p class="text-muted-gg mb-0 small">Monitoring fuel prices, availability, and complaints across Manolo Fortich.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4">
        <x-stat-card icon="bi-shop" label="Total Stations" :value="$stats['total_stations']" desc="Registered in the system" />
    </div>
    <div class="col-6 col-lg-4">
        <x-stat-card icon="bi-check-circle" label="Active Stations" :value="$stats['active_stations']" desc="Currently participating" />
    </div>
    <div class="col-6 col-lg-4">
        <x-stat-card icon="bi-x-circle" label="Inactive Stations" :value="$stats['inactive_stations']" desc="Not currently listed" />
    </div>
    <div class="col-6 col-lg-4">
        <x-stat-card icon="bi-fuel-pump" label="Enough Fuel" :value="$stats['enough_fuel']" desc="Stations with sufficient stock" />
    </div>
    <div class="col-6 col-lg-4">
        <x-stat-card icon="bi-exclamation-triangle" label="Almost Empty" :value="$stats['almost_empty']" desc="Stations running low" />
    </div>
    <div class="col-6 col-lg-4">
        <x-stat-card icon="bi-slash-circle" label="No Fuel" :value="$stats['no_fuel']" desc="Stations currently out" />
    </div>
    <div class="col-6 col-lg-4">
        <x-stat-card icon="bi-cash-stack" label="Average Fuel Price" value="{{ $stats['average_price'] ? '₱'.number_format($stats['average_price'],2) : '—' }}" desc="Across all fuel types" />
    </div>
    <div class="col-6 col-lg-4">
        <x-stat-card icon="bi-flag-fill" label="Total Complaints" :value="$stats['total_complaints']" desc="All time" />
    </div>
    <div class="col-6 col-lg-4">
        <x-stat-card icon="bi-hourglass-split" label="Pending Complaints" :value="$stats['pending_complaints']" desc="Awaiting review" />
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Recent Price Updates</h6>
            <a href="{{ route('lgu.analytics.index') }}" class="small">View analytics →</a>
        </div>
        @if($recentPriceUpdates->isEmpty())
            <x-empty-state icon="bi-inbox" title="No updates yet" />
        @else
        <div class="gg-table gg-table-responsive">
            <table class="table">
                <thead><tr><th>Station</th><th>Fuel Type</th><th>Price</th><th>Availability</th></tr></thead>
                <tbody>
                @foreach($recentPriceUpdates as $u)
                    <tr>
                        <td class="fw-semibold small">{{ $u->station->station_name }}</td>
                        <td class="small">{{ $u->fuelType->name }}</td>
                        <td class="small">₱{{ number_format($u->price,2) }}</td>
                        <td><x-availability-badge :status="$u->availability_status" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    <div class="col-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Recent Complaints</h6>
            <a href="{{ route('lgu.complaints.index') }}" class="small">View all →</a>
        </div>
        @if($recentComplaints->isEmpty())
            <x-empty-state icon="bi-flag" title="No complaints yet" />
        @else
        <div class="gg-table gg-table-responsive">
            <table class="table">
                <thead><tr><th>Ref.</th><th>Station</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($recentComplaints as $c)
                    <tr>
                        <td class="small">{{ $c->reference_no }}</td>
                        <td class="small">{{ optional($c->station)->station_name ?? 'General' }}</td>
                        <td><x-status-badge :status="$c->status" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
