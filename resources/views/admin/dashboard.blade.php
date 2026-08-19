@php
$sidebarItems = [
    ['route'=>'admin.dashboard','label'=>'Dashboard','icon'=>'bi-speedometer2'],
    ['route'=>'admin.stations.index','label'=>'Stations','icon'=>'bi-shop'],
    ['route'=>'admin.users.index','label'=>'Users','icon'=>'bi-people-fill'],
    ['route'=>'admin.verification.index','label'=>'Price Verification','icon'=>'bi-check2-square'],
    ['route'=>'admin.reports.index','label'=>'Reports','icon'=>'bi-file-earmark-text-fill'],
];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'admin.dashboard'])
@section('title', 'Admin Dashboard')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Admin Dashboard</h4>
        <p class="text-muted-gg mb-0 small">Overview of stations, personnel, and fuel price verification activity.</p>
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
        <x-stat-card icon="bi-people-fill" label="Station Personnel" :value="$stats['total_personnel']" desc="Authorized accounts" />
    </div>
    <div class="col-6 col-lg-4">
        <x-stat-card icon="bi-hourglass-split" label="Pending Updates" :value="$stats['pending_updates']" desc="Awaiting verification" />
    </div>
    <div class="col-6 col-lg-4">
        <x-stat-card icon="bi-check2-circle" label="Approved Updates" :value="$stats['approved_updates']" desc="Verified &amp; published" />
    </div>
    <div class="col-6 col-lg-4">
        <x-stat-card icon="bi-x-circle" label="Rejected Updates" :value="$stats['rejected_updates']" desc="Did not pass review" />
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0">Recent Fuel Price Updates</h6>
    <a href="{{ route('admin.verification.index') }}" class="small">View all →</a>
</div>

@if($recentUpdates->isEmpty())
    <x-empty-state icon="bi-inbox" title="No updates yet" message="Fuel price submissions from station personnel will appear here." />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Station</th><th>Submitted By</th><th>Gasoline</th><th>Diesel</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($recentUpdates as $u)
            <tr>
                <td class="fw-semibold">{{ $u->station->station_name }}</td>
                <td>{{ $u->submitter->name }}</td>
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
