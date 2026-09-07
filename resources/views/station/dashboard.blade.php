@php
$isManager = auth()->user()->isManager();
$sidebarItems = collect([
    ["route"=>"station.dashboard","label"=>"Dashboard","icon"=>"bi-speedometer2","perm"=>"view_dashboard"],
    ["route"=>"station.prices.edit","label"=>"Update Fuel Prices","icon"=>"bi-pencil-square","perm"=>"update_prices"],
    ["route"=>"station.history","label"=>"Price History","icon"=>"bi-clock-history","perm"=>"view_price_history"],
    ["route"=>"station.services.edit","label"=>"Station Services","icon"=>"bi-tools","perm"=>"manage_services"],
    ["route"=>"station.reports.index","label"=>"Reports","icon"=>"bi-file-earmark-text-fill","perm"=>"view_reports"],
])->filter(fn($i) => auth()->user()->hasStationPermission($i["perm"]))->values()->all();
if ($isManager) {
    $sidebarItems[] = ["route"=>"station.staff.index","label"=>"Staff Accounts","icon"=>"bi-people-fill"];
}
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.dashboard'])
@section('title', 'Station Dashboard')

@section('dashboard-content')
<p class="text-muted-gg small mb-1">Welcome back</p>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h4 class="mb-1">{{ $station->station_name }}</h4>
        <p class="text-muted-gg small mb-4"><i class="bi bi-geo-alt-fill me-1"></i>{{ $station->address }}, {{ $station->barangay }}</p>
    </div>
    <x-availability-badge :status="$station->overallAvailability()" />
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-cash-coin me-1"></i>Current Verified Prices</div>
            <div class="card-body">
                @forelse($currentPrices as $p)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small">{{ $p->fuelType->name }}</span>
                        <span class="fw-bold">₱{{ number_format($p->price,2) }}</span>
                        <x-availability-badge :status="$p->availability_status" />
                    </div>
                @empty
                    <p class="text-muted-gg mb-0">No prices set yet.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-people-fill me-1"></i>Station Overview</div>
            <div class="card-body">
                <p class="mb-1 small">Staff accounts: <strong>{{ $staffCount }}</strong></p>
                <p class="mb-1 small">Status: <x-status-badge :status="$station->status" /></p>
                <p class="mb-0 small">Your role: <strong>{{ auth()->user()->isManager() ? 'Manager' : 'Staff' }}</strong></p>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->hasStationPermission('update_prices'))
<a href="{{ route('station.prices.edit') }}" class="btn btn-brand btn-lg mb-4"><i class="bi bi-pencil-square me-1"></i>Update Fuel Prices</a>
@endif

<h6 class="mb-2">Recent Updates</h6>
@if($recentUpdates->isEmpty())
    <x-empty-state icon="bi-clock-history" title="No updates yet" message="Price and availability updates will appear here." />
@else
<div class="gg-table gg-table-responsive mb-4">
    <table class="table">
        <thead><tr><th>Fuel Type</th><th>Price</th><th>Availability</th><th>Updated By</th><th>Date</th></tr></thead>
        <tbody>
        @foreach($recentUpdates as $u)
            <tr>
                <td>{{ $u->fuelType->name }}</td>
                <td>₱{{ number_format($u->price,2) }}</td>
                <td><x-availability-badge :status="$u->availability_status" /></td>
                <td class="small">{{ $u->updater->name }}</td>
                <td class="text-muted-gg small">{{ $u->created_at->diffForHumans() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

<h6 class="mb-2">Related Complaints</h6>
@if($recentComplaints->isEmpty())
    <x-empty-state icon="bi-flag" title="No complaints for this station" />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Reference</th><th>Category</th><th>Subject</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        @foreach($recentComplaints as $c)
            <tr>
                <td class="small">{{ $c->reference_no }}</td>
                <td class="small">{{ \App\Models\Complaint::CATEGORIES[$c->category] }}</td>
                <td class="small">{{ $c->subject }}</td>
                <td><x-status-badge :status="$c->status" /></td>
                <td class="text-muted-gg small">{{ $c->created_at->format('M d, Y') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
