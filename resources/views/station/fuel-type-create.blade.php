@php
$sidebarItems = \App\Support\Nav::stationSidebar(auth()->user());
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.prices.edit'])
@section('title', 'Add Fuel Type')

@section('dashboard-content')
<h4 class="mb-1">Add Fuel Type</h4>
<p class="text-muted-gg small mb-3">Add a fuel your station offers that isn't listed yet (e.g. E10, E20). It becomes a normal part of your station's fuel lineup — shown in prices, availability, reports, analytics, and the public site.</p>

<div class="card mb-4" style="max-width:560px;">
    <div class="card-header">Currently Configured Fuel Types</div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            @foreach($fuelTypes as $ft)
                <span class="badge" style="background:var(--gg-accent-light); color:var(--gg-dark); font-weight:600;">{{ $ft->name }}@if($ft->specification) <span class="text-muted-gg fw-normal">({{ $ft->specification }})</span>@endif</span>
            @endforeach
        </div>
    </div>
</div>

<div class="card" style="max-width:560px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('station.fuel-types.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Fuel Type Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. E10" required>
                <div class="form-text">If this name already exists in the system, your station will just start using it — no duplicate is created.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Fuel Specification</label>
                <input type="text" name="specification" class="form-control" value="{{ old('specification') }}" placeholder="e.g. 10% Ethanol Blend">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2" maxlength="1000">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Initial Price</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" step="0.01" min="0" name="initial_price" class="form-control" value="{{ old('initial_price') }}" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Availability</label>
                <select name="availability_status" class="form-select">
                    <option value="enough">🟢 Has Enough Gasoline</option>
                    <option value="almost_empty">🟡 Almost Empty</option>
                    <option value="no_fuel">🔴 No Gasoline</option>
                </select>
            </div>
            <button class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Add Fuel Type</button>
            <a href="{{ route('station.prices.edit') }}" class="btn btn-outline-brand">Cancel</a>
        </form>
    </div>
</div>
@endsection
