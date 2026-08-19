@php
$sidebarItems = [
    ["route"=>"station.dashboard","label"=>"Dashboard","icon"=>"bi-speedometer2"],
    ["route"=>"station.prices.create","label"=>"Update Fuel Prices","icon"=>"bi-pencil-square"],
    ["route"=>"station.history","label"=>"Update History","icon"=>"bi-clock-history"],
];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.prices.create'])
@section('title', 'Update Fuel Prices')

@section('dashboard-content')
<h4 class="mb-1">Update Fuel Prices</h4>
<p class="text-muted-gg small mb-3">{{ $station->station_name }} — submitted prices are held as <strong>pending</strong> until an administrator verifies them.</p>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('station.prices.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Gasoline</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" step="0.01" min="0" name="gasoline_price" class="form-control" value="{{ old('gasoline_price') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Diesel</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" step="0.01" min="0" name="diesel_price" class="form-control" value="{{ old('diesel_price') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Premium</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" step="0.01" min="0" name="premium_price" class="form-control" value="{{ old('premium_price') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Regular</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" step="0.01" min="0" name="regular_price" class="form-control" value="{{ old('regular_price') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Effective Date</label>
                    <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date', date('Y-m-d')) }}" required>
                </div>
            </div>
            <p class="text-muted-gg small mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>Fill in at least one fuel price. All prices must be zero or greater.</p>
            <button class="btn btn-brand mt-3" onclick="return confirm('Submit these prices for administrator verification?');">
                <i class="bi bi-send-check me-1"></i>Submit for Verification
            </button>
        </form>
    </div>
</div>
@endsection
