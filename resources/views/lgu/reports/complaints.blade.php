@php
$sidebarItems = \App\Support\Nav::lguSidebar();
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.reports.index'])
@section('title', 'Complaint Report')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Complaint Report</h4>
        <p class="text-muted-gg small mb-0">Generated {{ now()->format('F d, Y g:i A') }}</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('lgu.reports.complaints', array_merge(request()->query(), ['export'=>'csv'])) }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
        <button class="btn btn-outline-brand btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print Complaint Report</button>
    </div>
</div>

<div class="d-none d-print-block mb-3">
    <h5 class="mb-0">GeoGas ManFort</h5>
    <div class="fw-bold">Complaint Report{{ $selectedStation ? ' — '.$selectedStation->station_name : '' }}</div>
    @if($selectedStation)
        <div class="small text-muted-gg">{{ $selectedStation->address }}, {{ $selectedStation->barangay }}</div>
    @endif
    <div class="small text-muted-gg">Generated: {{ now()->format('F d, Y g:i A') }}</div>
    <hr>
</div>

<form method="GET" class="row g-2 mb-3 no-print">
    <div class="col-md-3">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="pending" @selected(request('status')=='pending')>Pending</option>
            <option value="under_review" @selected(request('status')=='under_review')>Under Review</option>
            <option value="resolved" @selected(request('status')=='resolved')>Resolved</option>
        </select>
    </div>
    <div class="col-md-4">
        <select name="station_id" class="form-select" onchange="this.form.submit()">
            <option value="">All Gasoline Stations</option>
            @foreach($stations as $s)
                <option value="{{ $s->id }}" @selected(request('station_id')==$s->id)>{{ $s->station_name }}</option>
            @endforeach
        </select>
    </div>
</form>

@if($complaints->isEmpty())
    <x-empty-state icon="bi-flag" title="No complaints found" message="Try a different filter." />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Reference</th><th>Category</th><th>Station</th><th>Area</th><th>Submitted</th><th>Status</th><th>Action Taken</th><th>Resolved</th></tr></thead>
        <tbody>
        @foreach($complaints as $c)
            <tr>
                <td class="small">{{ $c->reference_no }}</td>
                <td class="small">{{ \App\Models\Complaint::CATEGORIES[$c->category] }}</td>
                <td class="small">{{ optional($c->station)->station_name ?? 'General' }}</td>
                <td class="small">{{ optional($c->station)->barangay ?? '—' }}</td>
                <td class="text-muted-gg small">{{ $c->created_at->format('M d, Y') }}</td>
                <td><x-status-badge :status="$c->status" /></td>
                <td class="small">{{ $c->lgu_response ?: '—' }}</td>
                <td class="text-muted-gg small">{{ optional($c->resolved_at)->format('M d, Y') ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
