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
@section('title', 'Station List Report')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Station List Report</h4>
        <p class="text-muted-gg small mb-0">Generated {{ now()->format('F d, Y') }}</p>
    </div>
    <button class="btn btn-outline-brand no-print" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
</div>
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Station</th><th>Address</th><th>Status</th><th>Current Gasoline</th><th>Current Diesel</th></tr></thead>
        <tbody>
        @foreach($stations as $s)
            <tr>
                <td class="fw-semibold">{{ $s->station_name }}</td>
                <td class="text-muted-gg">{{ $s->address }}, {{ $s->barangay }}</td>
                <td><x-status-badge :status="$s->status" /></td>
                <td>{{ $s->latestApprovedPrice ? '₱'.number_format($s->latestApprovedPrice->gasoline_price,2) : '—' }}</td>
                <td>{{ $s->latestApprovedPrice ? '₱'.number_format($s->latestApprovedPrice->diesel_price,2) : '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
