@php
$sidebarItems = \App\Support\Nav::stationSidebar(auth()->user());
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.reports.index'])
@section('title', 'Station Reports')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Reports — {{ $station->station_name }}</h4>
        <p class="text-muted-gg small mb-0">Scoped to your own station only.</p>
    </div>
    <div class="d-flex gap-2 no-print">
        @if(auth()->user()->hasStationPermission('generate_reports'))
            <a href="{{ route('station.reports.index', ['export'=>'csv']) }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-filetype-csv me-1"></i>Export CSV</a>
        @endif
        @if(auth()->user()->hasStationPermission('print_reports'))
            <button class="btn btn-outline-brand btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
        @endif
    </div>
</div>

<div class="d-none d-print-block mb-3">
    <h5 class="mb-0">GeoGas ManFort</h5>
    <div class="fw-bold">{{ $station->station_name }}</div>
    <div class="small text-muted-gg">Reporting Period: All available records &middot; Generated: {{ now()->format('F d, Y g:i A') }}</div>
    <hr>
</div>

<h6 class="mb-2">Fuel Price &amp; Availability History</h6>
<div class="gg-table gg-table-responsive mb-4">
    <table class="table">
        <thead><tr><th>Date</th><th>Fuel Type</th><th>Price</th><th>Availability</th><th>Updated By</th></tr></thead>
        <tbody>
        @forelse($priceHistory as $p)
            <tr>
                <td class="small">{{ $p->created_at->format('M d, Y g:i A') }}</td>
                <td>{{ $p->fuelType->name }}</td>
                <td>₱{{ number_format($p->price,2) }}</td>
                <td><x-availability-badge :status="$p->availability_status" /></td>
                <td class="small">{{ $p->updater->name }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted-gg text-center">No history yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<h6 class="mb-2">Complaints Related to This Station</h6>
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Reference</th><th>Category</th><th>Subject</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        @forelse($complaints as $c)
            <tr>
                <td class="small">{{ $c->reference_no }}</td>
                <td class="small">{{ \App\Models\Complaint::CATEGORIES[$c->category] }}</td>
                <td class="small">{{ $c->subject }}</td>
                <td><x-status-badge :status="$c->status" /></td>
                <td class="text-muted-gg small">{{ $c->created_at->format('M d, Y') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted-gg text-center">No complaints on record.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
