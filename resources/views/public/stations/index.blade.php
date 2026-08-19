@extends('layouts.app')
@section('title', 'Gasoline Stations')

@section('content')
@php
    $lowestGasoline = $stations->getCollection()->filter(fn($s) => $s->latestApprovedPrice)
        ->min(fn($s) => $s->latestApprovedPrice->gasoline_price);
@endphp

<h4 class="mb-1">Gasoline Stations</h4>
<p class="text-muted-gg mb-4">Compare verified fuel prices from participating stations.</p>

<form method="GET" class="row g-2 mb-4">
    <div class="col-md-8">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted-gg"></i></span>
            <input type="text" name="q" class="form-control border-start-0" placeholder="Search by station name or location" value="{{ request('q') }}">
        </div>
    </div>
    <div class="col-md-2">
        <button class="btn btn-brand w-100">Search</button>
    </div>
    @if(request('q'))
        <div class="col-md-2">
            <a href="{{ route('stations.index') }}" class="btn btn-outline-brand w-100">Clear</a>
        </div>
    @endif
</form>

@if($stations->isEmpty())
    <x-empty-state icon="bi-shop" title="No gasoline stations found" message="Try changing your search or filter.">
        <a href="{{ route('stations.index') }}" class="btn btn-outline-brand btn-sm">Clear Filters</a>
    </x-empty-state>
@else
<div class="row g-3">
    @foreach($stations as $station)
        <div class="col-md-6 col-lg-4">
            <x-station-card :station="$station" :isLowest="$station->latestApprovedPrice && $lowestGasoline && (float) $station->latestApprovedPrice->gasoline_price === (float) $lowestGasoline" />
        </div>
    @endforeach
</div>
<div class="mt-4">{{ $stations->links() }}</div>
@endif
@endsection
