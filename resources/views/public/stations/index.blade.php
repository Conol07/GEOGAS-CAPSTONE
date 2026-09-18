@extends('layouts.app')
@section('title', 'Stations')

@section('content')
<h4 class="mb-1">Gasoline Stations</h4>
<p class="text-muted-gg mb-4">Search, filter by area, and compare fuel prices from participating stations.</p>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted-gg"></i></span>
            <input type="text" name="q" class="form-control border-start-0" placeholder="Search by name or location" value="{{ request('q') }}">
        </div>
    </div>
    <div class="col-md-2">
        <select name="barangay" class="form-select" onchange="this.form.submit()">
            <option value="">All Areas</option>
            @foreach($barangays as $b)
                <option value="{{ $b }}" @selected(request('barangay')==$b)>{{ $b }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select name="availability" class="form-select" onchange="this.form.submit()">
            <option value="">Any Availability</option>
            <option value="enough" @selected(request('availability')=='enough')>🟢 Has Enough</option>
            <option value="almost_empty" @selected(request('availability')=='almost_empty')>🟡 Almost Empty</option>
            <option value="no_fuel" @selected(request('availability')=='no_fuel')>🔴 No Gasoline</option>
        </select>
    </div>
    <div class="col-md-2">
        <select name="fuel_type_id" class="form-select" onchange="this.form.submit()">
            @foreach($fuelTypes as $ft)
                <option value="{{ $ft->id }}" @selected($fuelTypeId==$ft->id)>{{ $ft->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select name="sort" class="form-select" onchange="this.form.submit()">
            <option value="name" @selected($sort=='name')>Sort: Name</option>
            <option value="lowest" @selected($sort=='lowest')>Sort: Lowest Price</option>
            <option value="highest" @selected($sort=='highest')>Sort: Highest Price</option>
        </select>
    </div>
</form>

@if($areaStats)
    <div class="card mb-4" style="border-color:var(--gg-accent); background:var(--gg-accent-light);">
        <div class="card-body">
            <h6 class="mb-3"><i class="bi bi-geo-alt-fill me-1"></i>{{ $areaStats['barangay'] }} — Area Overview ({{ $fuelTypes->firstWhere('id', $fuelTypeId)->name ?? '' }})</h6>
            <div class="row g-3 text-center">
                <div class="col-6 col-md-3">
                    <div class="small text-muted-gg">Stations</div>
                    <div class="fw-bold">{{ $areaStats['station_count'] }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small text-muted-gg">Lowest</div>
                    <div class="fw-bold">₱{{ number_format($areaStats['lowest'],2) }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small text-muted-gg">Average</div>
                    <div class="fw-bold">₱{{ number_format($areaStats['average'],2) }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small text-muted-gg">Highest</div>
                    <div class="fw-bold">₱{{ number_format($areaStats['highest'],2) }}</div>
                </div>
            </div>
        </div>
    </div>
@endif

@if($paginated->isEmpty())
    <x-empty-state icon="bi-shop" title="No gasoline stations found" message="Try changing your search or filter.">
        <a href="{{ route('stations.index') }}" class="btn btn-outline-brand btn-sm">Clear Filters</a>
    </x-empty-state>
@else
<div class="row g-3">
    @foreach($paginated as $station)
        <div class="col-md-6 col-lg-4">
            <x-station-card :station="$station" />
        </div>
    @endforeach
</div>
<div class="mt-4">{{ $paginated->links() }}</div>
@endif
@endsection
