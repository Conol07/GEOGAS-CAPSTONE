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

<div class="card mt-3">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('station.prices.update') }}">
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
                            <input type="number" step="0.01" min="0" name="prices[{{ $ft->id }}][price]" class="form-control" value="{{ old("prices.{$ft->id}.price") }}">
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
