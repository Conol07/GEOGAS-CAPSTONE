@php
$sidebarItems = \App\Support\Nav::lguSidebar();
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.reports.index'])
@section('title', 'Station Record')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">Station Record</h4>
        <p class="text-muted-gg small mb-0">Full record for a single gasoline station — information, prices, availability, services, and complaints.</p>
    </div>
    @if($station)
        <button class="btn btn-outline-brand btn-sm no-print" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print Station Record</button>
    @endif
</div>

<form method="GET" class="row g-2 mb-4 no-print">
    <div class="col-md-4">
        <select name="station_id" class="form-select" onchange="this.form.submit()">
            <option value="">— Select Station —</option>
            @foreach($stations as $s)
                <option value="{{ $s->id }}" @selected($station && $station->id == $s->id)>{{ $s->station_name }}</option>
            @endforeach
        </select>
    </div>
</form>

@if(! $station)
    <x-empty-state icon="bi-file-earmark-text" title="Select a station" message="Choose a gasoline station above to view and print its full record." />
@else
    <div class="d-none d-print-block mb-3">
        <h5 class="mb-0">GeoGas ManFort</h5>
        <div class="fw-bold">Station Record</div>
        <div class="small text-muted-gg">Generated: {{ now()->format('F d, Y g:i A') }}</div>
        <hr>
    </div>

    <div class="card mb-3">
        <div class="card-header">Station Information</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <p class="mb-1"><strong>{{ $station->station_name }}</strong></p>
                    <p class="mb-1 small">{{ $station->address }}, {{ $station->barangay }}, {{ $station->municipality }}, {{ $station->province }}</p>
                    <p class="mb-1 small">Contact: {{ $station->contact_number ?? '—' }} &middot; Email: {{ $station->email ?? '—' }}</p>
                    <p class="mb-1 small">Coordinates: {{ $station->latitude }}, {{ $station->longitude }}</p>
                    <p class="mb-0 small">Status: <x-status-badge :status="$station->status" /> &middot; Availability: <x-availability-badge :status="$station->overallAvailability()" /></p>
                </div>
                @if($station->photo_path)
                <div class="col-md-4">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($station->photo_path) }}" alt="{{ $station->station_name }}" class="img-fluid rounded">
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Current Fuel Prices</div>
        <div class="card-body">
            @forelse($currentPrices as $p)
                <div class="d-flex justify-content-between border-bottom py-1 small">
                    <span>{{ $p->fuelType->name }}</span>
                    <span>₱{{ number_format($p->price,2) }}</span>
                    <x-availability-badge :status="$p->availability_status" />
                </div>
            @empty
                <p class="text-muted-gg small mb-0">No prices on record.</p>
            @endforelse
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Station Services</div>
        <div class="card-body">
            @php $active = $station->services->where('available', true); @endphp
            @if($active->isEmpty())
                <p class="text-muted-gg small mb-0">No services listed.</p>
            @else
                <div class="d-flex flex-wrap gap-2">
                    @foreach($active as $svc)
                        <span class="badge" style="background:var(--gg-accent-light); color:var(--gg-dark);">{{ $svc->label }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Price Update History (last 50)</div>
        <div class="gg-table gg-table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Date</th><th>Fuel Type</th><th>Price</th><th>Availability</th><th>Updated By</th></tr></thead>
                <tbody>
                @forelse($priceHistory as $p)
                    <tr>
                        <td class="small">{{ $p->created_at->format('M d, Y g:i A') }}</td>
                        <td class="small">{{ $p->fuelType->name }}</td>
                        <td class="small">₱{{ number_format($p->price,2) }}</td>
                        <td><x-availability-badge :status="$p->availability_status" /></td>
                        <td class="small">{{ $p->updater->name }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted-gg text-center">No history yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Complaint History</div>
        <div class="gg-table gg-table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Reference</th><th>Category</th><th>Submitted</th><th>Status</th><th>Action Taken</th></tr></thead>
                <tbody>
                @forelse($complaints as $c)
                    <tr>
                        <td class="small">{{ $c->reference_no }}</td>
                        <td class="small">{{ \App\Models\Complaint::CATEGORIES[$c->category] }}</td>
                        <td class="small">{{ $c->created_at->format('M d, Y') }}</td>
                        <td><x-status-badge :status="$c->status" /></td>
                        <td class="small">{{ $c->lgu_response ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted-gg text-center">No complaints on record.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
