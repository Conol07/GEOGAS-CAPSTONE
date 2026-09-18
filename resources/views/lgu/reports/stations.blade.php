@php
$sidebarItems = \App\Support\Nav::lguSidebar();
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.reports.index'])
@section('title', 'Station Report')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Station Report</h4>
        <p class="text-muted-gg small mb-0">Generated {{ now()->format('F d, Y g:i A') }}</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('lgu.reports.stations', ['export'=>'csv']) }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
        <button class="btn btn-outline-brand btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
    </div>
</div>
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Station</th><th>Address</th><th>Status</th><th>Availability</th><th>Services</th></tr></thead>
        <tbody>
        @foreach($stations as $s)
            <tr>
                <td class="fw-semibold">{{ $s->station_name }}</td>
                <td class="small">{{ $s->address }}, {{ $s->barangay }}</td>
                <td><x-status-badge :status="$s->status" /></td>
                <td><x-availability-badge :status="$s->overallAvailability()" /></td>
                <td class="small">{{ $s->services->where('available', true)->count() }} active</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
