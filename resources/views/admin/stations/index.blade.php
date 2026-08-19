@php
$sidebarItems = [
    ["route"=>"admin.dashboard","label"=>"Dashboard","icon"=>"bi-speedometer2"],
    ["route"=>"admin.stations.index","label"=>"Stations","icon"=>"bi-shop"],
    ["route"=>"admin.users.index","label"=>"Users","icon"=>"bi-people-fill"],
    ["route"=>"admin.verification.index","label"=>"Price Verification","icon"=>"bi-check2-square"],
    ["route"=>"admin.reports.index","label"=>"Reports","icon"=>"bi-file-earmark-text-fill"],
];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'admin.stations.index'])
@section('title', 'Station Management')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Station Management</h4>
        <p class="text-muted-gg small mb-0">Register and manage participating gasoline stations.</p>
    </div>
    <a href="{{ route('admin.stations.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Register New Station</a>
</div>

<form method="GET" class="mb-3">
    <div class="input-group" style="max-width:420px;">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted-gg"></i></span>
        <input type="text" name="q" class="form-control border-start-0" placeholder="Search stations" value="{{ request('q') }}">
    </div>
</form>

@if($stations->isEmpty())
    <x-empty-state icon="bi-shop" title="No stations found" message="Try adjusting your search, or register a new station." />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Name</th><th>Address</th><th>Status</th><th class="no-print">Actions</th></tr></thead>
        <tbody>
        @foreach($stations as $s)
            <tr>
                <td class="fw-semibold">{{ $s->station_name }}</td>
                <td class="text-muted-gg">{{ $s->address }}</td>
                <td><x-status-badge :status="$s->status" /></td>
                <td>
                    <a href="{{ route('admin.stations.edit', $s) }}" class="btn btn-sm btn-outline-brand"><i class="bi bi-pencil"></i> Edit</a>
                    <form action="{{ route('admin.stations.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Deactivate this station?');">
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
