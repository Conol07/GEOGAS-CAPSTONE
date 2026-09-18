@php
$sidebarItems = \App\Support\Nav::lguSidebar();
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.reports.index'])
@section('title', 'Reports')

@section('dashboard-content')
<h4 class="mb-1">Reports</h4>
<p class="text-muted-gg small mb-3">View on screen, print, or export to CSV. All reports pull live data.</p>
<div class="row g-3">
    <div class="col-md-4">
        <a href="{{ route('lgu.reports.fuel-prices') }}" class="card text-decoration-none h-100">
            <div class="card-body p-4">
                <div class="gg-stat-icon mb-3"><i class="bi bi-cash-coin"></i></div>
                <h6 class="mb-1">Fuel Price Report</h6>
                <p class="text-muted-gg small mb-0">Prices by station, fuel type, and barangay.</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('lgu.reports.stations') }}" class="card text-decoration-none h-100">
            <div class="card-body p-4">
                <div class="gg-stat-icon mb-3"><i class="bi bi-shop"></i></div>
                <h6 class="mb-1">Station Report</h6>
                <p class="text-muted-gg small mb-0">Registered stations, status, and availability.</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('lgu.reports.complaints') }}" class="card text-decoration-none h-100">
            <div class="card-body p-4">
                <div class="gg-stat-icon mb-3"><i class="bi bi-flag-fill"></i></div>
                <h6 class="mb-1">Complaint Report</h6>
                <p class="text-muted-gg small mb-0">All complaints — filterable by gasoline station.</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('lgu.reports.station-record') }}" class="card text-decoration-none h-100">
            <div class="card-body p-4">
                <div class="gg-stat-icon mb-3"><i class="bi bi-file-earmark-text-fill"></i></div>
                <h6 class="mb-1">Station Record</h6>
                <p class="text-muted-gg small mb-0">Full printable record for one gasoline station.</p>
            </div>
        </a>
    </div>
</div>
<p class="text-muted-gg small mt-4"><i class="bi bi-info-circle me-1"></i>For price trends and area comparisons, see <a href="{{ route('lgu.analytics.index') }}">Analytics</a>.</p>
@endsection
