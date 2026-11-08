@php
$sidebarItems = \App\Support\Nav::stationSidebar(auth()->user());
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.prices.edit'])
@section('title', 'Update Fuel Prices')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-1">
    <div>
        <h4 class="mb-1">Update Fuel Prices &amp; Availability</h4>
        <p class="text-muted-gg small mb-0">{{ $station->station_name }} — changes are live on the public map immediately. No LGU approval required.</p>
    </div>
    <a href="{{ route('station.fuel-types.create') }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Fuel Type</a>
</div>

@if(session('price_jumps'))
    <div class="alert alert-warning mt-3" role="alert">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Please confirm these large price changes</div>
        <ul class="mb-2 ps-4">
            @foreach(session('price_jumps') as $j)
                <li>{{ $j['fuel'] }}: ₱{{ number_format($j['old'],2) }} → ₱{{ number_format($j['new'],2) }} ({{ $j['pct'] > 0 ? '+' : '' }}{{ $j['pct'] }}%)</li>
            @endforeach
        </ul>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="confirm_jump" value="1" id="confirmJump" form="priceForm" required>
            <label class="form-check-label" for="confirmJump">These prices are correct, publish them.</label>
        </div>
    </div>
@endif

@if($currentPrices->isNotEmpty())
    @php $lastAt = $currentPrices->max('created_at'); @endphp
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 p-3 border rounded bg-white">
        <div class="small"><i class="bi bi-clock-history me-1"></i>Last updated {{ $lastAt->diffForHumans() }} <x-stale-badge :at="$lastAt" /></div>
        <form method="POST" action="{{ route('station.prices.confirm') }}" onsubmit="return confirm('Confirm that all current prices and availability are still correct?');">
            @csrf
            <button class="btn btn-outline-brand btn-sm"><i class="bi bi-check2-circle me-1"></i>Prices unchanged — confirm</button>
        </form>
    </div>
@endif

<div class="card mt-3">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('station.prices.update') }}" id="priceForm">
            @csrf
            @foreach($fuelTypes as $ft)
                @php $current = $currentPrices->get($ft->id); @endphp
                <div class="row g-3 align-items-end border-bottom pb-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label mb-0">{{ $ft->name }}</label>
                        @if($ft->specification)
                            <div class="text-muted-gg" style="font-size:.72rem;">{{ $ft->specification }}</div>
                        @endif
                        @if($current)
                            <div class="text-muted-gg small">Current: ₱{{ number_format($current->price,2) }}</div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">New Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" min="{{ config('geogas.price_min') }}" max="{{ config('geogas.price_max') }}" name="prices[{{ $ft->id }}][price]" class="form-control" value="{{ old("prices.{$ft->id}.price") }}">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small">Availability</label>
                        <select name="prices[{{ $ft->id }}][availability_status]" class="form-select">
                            <option value="enough" {{ optional($current)->availability_status=='enough' ? 'selected' : '' }}>🟢 Has Enough Gasoline</option>
                            <option value="almost_empty" {{ optional($current)->availability_status=='almost_empty' ? 'selected' : '' }}>🟡 Almost Empty</option>
                            <option value="no_fuel" {{ optional($current)->availability_status=='no_fuel' ? 'selected' : '' }}>🔴 No Gasoline</option>
                        </select>
                    </div>
                </div>
            @endforeach

            <p class="text-muted-gg small mb-3"><i class="bi bi-info-circle me-1"></i>Leave a fuel type's price blank to skip updating it this time.</p>
            <button class="btn btn-brand" onclick="return confirm('Update these fuel prices now? Changes go live immediately.');">
                <i class="bi bi-send-check me-1"></i>Update Now
            </button>
        </form>
    </div>
</div>
@endsection
