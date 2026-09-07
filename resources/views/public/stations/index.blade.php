@extends('layouts.app')
@section('title', 'Gasoline Stations')

@section('content')
<h4 class="mb-1">Gasoline Stations</h4>
<p class="text-muted-gg mb-4">Compare verified fuel prices and availability from participating stations.</p>

<form method="GET" class="row g-2 mb-4">
    <div class="col-md-5">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted-gg"></i></span>
            <input type="text" name="q" class="form-control border-start-0" placeholder="Search by station name or location" value="{{ request('q') }}">
        </div>
    </div>
    <div class="col-md-3">
        <select name="barangay" class="form-select" onchange="this.form.submit()">
            <option value="">All Barangays</option>
            @foreach($barangays as $b)
                <option value="{{ $b }}" @selected(request('barangay')==$b)>{{ $b }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="availability" class="form-select" onchange="this.form.submit()">
            <option value="">Any Availability</option>
            <option value="enough" @selected(request('availability')=='enough')>🟢 Has Enough Gasoline</option>
            <option value="almost_empty" @selected(request('availability')=='almost_empty')>🟡 Almost Empty</option>
            <option value="no_fuel" @selected(request('availability')=='no_fuel')>🔴 No Gasoline</option>
        </select>
    </div>
    <div class="col-md-1"><button class="btn btn-brand w-100"><i class="bi bi-search"></i></button></div>
</form>

@if($stations->isEmpty())
    <x-empty-state icon="bi-shop" title="No gasoline stations found" message="Try changing your search or filter.">
        <a href="{{ route('stations.index') }}" class="btn btn-outline-brand btn-sm">Clear Filters</a>
    </x-empty-state>
@else
<div class="row g-3">
    @foreach($stations as $station)
        <div class="col-md-6 col-lg-4">
            <x-station-card :station="$station" />
        </div>
    @endforeach
</div>
<div class="mt-4">{{ $stations->links() }}</div>
@endif
@endsection
