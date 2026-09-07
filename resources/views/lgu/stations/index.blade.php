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
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.stations.index'])
@section('title', 'Station Management')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Station Management</h4>
        <p class="text-muted-gg small mb-0">Register stations and their manager accounts. New stations appear on the public map immediately.</p>
    </div>
    <a href="{{ route('lgu.stations.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Register New Station</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="text" name="q" class="form-control" placeholder="Search stations" value="{{ request('q') }}">
    </div>
    <div class="col-md-3">
        <select name="barangay" class="form-select" onchange="this.form.submit()">
            <option value="">All Barangays</option>
            @foreach($barangays as $b)
                <option value="{{ $b }}" @selected(request('barangay')==$b)>{{ $b }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-outline-brand w-100">Search</button></div>
</form>

@if($stations->isEmpty())
    <x-empty-state icon="bi-shop" title="No stations found" message="Try adjusting your search, or register a new station." />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Name</th><th>Barangay</th><th>Availability</th><th>Status</th><th class="no-print">Actions</th></tr></thead>
        <tbody>
        @foreach($stations as $s)
            <tr>
                <td class="fw-semibold">{{ $s->station_name }}</td>
                <td class="text-muted-gg small">{{ $s->barangay }}</td>
                <td><x-availability-badge :status="$s->overallAvailability()" /></td>
                <td><x-status-badge :status="$s->status" /></td>
                <td>
                    <a href="{{ route('lgu.stations.edit', $s) }}" class="btn btn-sm btn-outline-brand"><i class="bi bi-pencil"></i> Edit</a>
                    <form action="{{ route('lgu.stations.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Deactivate this station?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-slash-circle"></i> Deactivate</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $stations->links() }}</div>
@endif
@endsection
