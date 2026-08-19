@php
$sidebarItems = [
    ["route"=>"admin.dashboard","label"=>"Dashboard","icon"=>"bi-speedometer2"],
    ["route"=>"admin.stations.index","label"=>"Stations","icon"=>"bi-shop"],
    ["route"=>"admin.users.index","label"=>"Users","icon"=>"bi-people-fill"],
    ["route"=>"admin.verification.index","label"=>"Price Verification","icon"=>"bi-check2-square"],
    ["route"=>"admin.reports.index","label"=>"Reports","icon"=>"bi-file-earmark-text-fill"],
];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'admin.reports.index'])
@section('title', 'Fuel Price Update History Report')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Fuel Price Update History</h4>
        <p class="text-muted-gg small mb-0">Generated {{ now()->format('F d, Y') }}</p>
    </div>
    <button class="btn btn-outline-brand no-print" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
</div>

<form method="GET" class="row g-2 mb-3 no-print">
    <div class="col-md-3">
        <select name="status" class="form-select">
            @foreach(['all'=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$l)
                <option value="{{ $k }}" @selected($status==$k)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-brand w-100">Filter</button></div>
</form>

<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Station</th><th>Submitted By</th><th>Verified By</th><th>Gasoline</th><th>Diesel</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        @foreach($history as $h)
            <tr>
                <td class="fw-semibold">{{ $h->station->station_name }}</td>
                <td>{{ $h->submitter->name }}</td>
                <td>{{ optional($h->verifier)->name ?? '—' }}</td>
                <td>₱{{ number_format($h->gasoline_price,2) }}</td>
                <td>₱{{ number_format($h->diesel_price,2) }}</td>
                <td><x-status-badge :status="$h->status" /></td>
                <td class="text-muted-gg">{{ $h->created_at->format('M d, Y') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
