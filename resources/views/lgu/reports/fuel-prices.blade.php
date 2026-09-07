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
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.reports.index'])
@section('title', 'Fuel Price Report')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Fuel Price Report</h4>
        <p class="text-muted-gg small mb-0">Generated {{ now()->format('F d, Y g:i A') }}</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('lgu.reports.fuel-prices', array_merge(request()->query(), ['export'=>'csv'])) }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
        <button class="btn btn-outline-brand btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
    </div>
</div>

<form method="GET" class="row g-2 mb-3 no-print">
    <div class="col-md-3">
        <select name="barangay" class="form-select" onchange="this.form.submit()">
            <option value="">All Barangays</option>
            @foreach($barangays as $b)
                <option value="{{ $b }}" @selected(request('barangay')==$b)>{{ $b }}</option>
            @endforeach
        </select>
    </div>
</form>

<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Station</th><th>Barangay</th><th>Fuel Type</th><th>Price</th><th>Availability</th><th>Updated</th></tr></thead>
        <tbody>
        @foreach($prices as $p)
            <tr>
                <td class="fw-semibold">{{ $p->station->station_name }}</td>
                <td class="small">{{ $p->station->barangay }}</td>
                <td class="small">{{ $p->fuelType->name }}</td>
                <td>₱{{ number_format($p->price,2) }}</td>
                <td><x-availability-badge :status="$p->availability_status" /></td>
                <td class="text-muted-gg small">{{ $p->created_at->format('M d, Y g:i A') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
