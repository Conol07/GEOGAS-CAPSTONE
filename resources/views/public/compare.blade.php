@extends('layouts.app')
@section('title', 'Compare Fuel Prices')

@section('content')
@php
    $lowestGasoline = $stations->filter(fn($s) => $s->latestApprovedPrice)
        ->min(fn($s) => $s->latestApprovedPrice->gasoline_price);
@endphp

<h4 class="mb-1">Compare Fuel Prices</h4>
<p class="text-muted-gg mb-4">Compare the latest administrator-verified fuel prices across participating stations.</p>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="sort" class="form-select" onchange="this.form.submit()">
            <option value="name" @selected($sort=='name')>Sort: Station Name</option>
            <option value="lowest" @selected($sort=='lowest')>Sort: Lowest Gasoline Price</option>
            <option value="highest" @selected($sort=='highest')>Sort: Highest Gasoline Price</option>
            <option value="updated" @selected($sort=='updated')>Sort: Latest Update</option>
        </select>
    </div>
</form>

@if($stations->isEmpty())
    <x-empty-state icon="bi-bar-chart-line" title="No stations to compare" message="Check back once stations are registered." />
@else
<div class="gg-table gg-table-responsive">
<table class="table">
    <thead>
        <tr>
            <th>Station</th>
            <th>Location</th>
            <th class="text-end">Gasoline</th>
            <th class="text-end">Diesel</th>
            <th>Last Updated</th>
            <th class="no-print">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stations as $station)
            @php $price = $station->latestApprovedPrice; @endphp
            <tr>
                <td class="fw-semibold">{{ $station->station_name }}</td>
                <td class="text-muted-gg small">{{ $station->barangay }}, {{ $station->municipality }}</td>
                <td class="text-end">
                    @if($price)
                        ₱{{ number_format($price->gasoline_price,2) }}
                        @if($lowestGasoline && (float)$price->gasoline_price === (float)$lowestGasoline)
                            <span class="gg-lowest-badge">LOWEST</span>
                        @endif
                    @else
                        —
                    @endif
                </td>
                <td class="text-end">{{ $price ? '₱'.number_format($price->diesel_price,2) : '—' }}</td>
                <td class="text-muted-gg small">{{ optional($price)->effective_date?->format('M d, Y') ?? '—' }}</td>
                <td><a href="{{ route('stations.show', $station) }}" class="btn btn-sm btn-outline-brand">View</a></td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>
@endif
@endsection
