@extends('layouts.app')
@section('title', 'Compare Fuel Prices')

@section('content')
<h4 class="mb-1">Compare Fuel Prices</h4>
<p class="text-muted-gg mb-4">Compare the latest station-reported fuel prices, sorted however you like.</p>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="fuel_type_id" class="form-select" onchange="this.form.submit()">
            @foreach($fuelTypes as $ft)
                <option value="{{ $ft->id }}" @selected($fuelTypeId==$ft->id)>{{ $ft->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="sort" class="form-select" onchange="this.form.submit()">
            <option value="lowest" @selected($sort=='lowest')>Sort: Lowest Price</option>
            <option value="highest" @selected($sort=='highest')>Sort: Highest Price</option>
            <option value="name" @selected($sort=='name')>Sort: Station Name</option>
            <option value="updated" @selected($sort=='updated')>Sort: Latest Update</option>
        </select>
    </div>
</form>

@if($prices->isEmpty())
    <x-empty-state icon="bi-bar-chart-line" title="No prices to compare" message="Check back once stations report this fuel type." />
@else
<div class="gg-table gg-table-responsive">
<table class="table">
    <thead>
        <tr>
            <th>Station</th>
            <th>Barangay</th>
            <th class="text-end">Price</th>
            <th>Availability</th>
            <th>Last Updated</th>
            <th class="no-print">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prices as $price)
            <tr>
                <td class="fw-semibold">{{ $price->station->station_name }}</td>
                <td class="text-muted-gg small">{{ $price->station->barangay }}</td>
                <td class="text-end">
                    ₱{{ number_format($price->price,2) }}
                    @if($loop->first && $sort === 'lowest')
                        <span class="gg-lowest-badge">LOWEST</span>
                    @endif
                </td>
                <td><x-availability-badge :status="$price->availability_status" /></td>
                <td class="text-muted-gg small">{{ $price->created_at->diffForHumans() }}</td>
                <td><a href="{{ route('stations.show', $price->station) }}" class="btn btn-sm btn-outline-brand">View</a></td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>
@endif
@endsection
