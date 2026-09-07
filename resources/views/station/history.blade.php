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
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.history'])
@section('title', 'Price History')

@section('dashboard-content')
<h4 class="mb-1">Fuel Price &amp; Availability History</h4>
<p class="text-muted-gg small mb-3">Every update your station has made — this doubles as your audit trail.</p>

@if($updates->isEmpty())
    <x-empty-state icon="bi-clock-history" title="No updates yet" message="Prices you update will show up here." />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Date</th><th>Fuel Type</th><th>Price</th><th>Change</th><th>Availability</th><th>Updated By</th></tr></thead>
        <tbody>
        @foreach($updates as $u)
            <tr>
                <td class="small">{{ $u->created_at->format('M d, Y g:i A') }}</td>
                <td>{{ $u->fuelType->name }}</td>
                <td>₱{{ number_format($u->price,2) }}</td>
                <td><x-price-change :price="$u" /></td>
                <td><x-availability-badge :status="$u->availability_status" /></td>
                <td class="small">{{ $u->updater->name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $updates->links() }}</div>
@endif
@endsection
