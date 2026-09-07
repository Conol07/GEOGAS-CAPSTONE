@extends('layouts.app')
@section('title', 'Cheapest Fuel Finder')

@section('content')
<h4 class="mb-1">Cheapest Fuel Finder</h4>
<p class="text-muted-gg mb-4">Select a fuel type to find the cheapest available station.</p>

<form method="GET" class="row g-2 mb-4">
    <div class="col-md-3">
        <select name="fuel_type_id" class="form-select" onchange="this.form.submit()">
            @foreach($fuelTypes as $ft)
                <option value="{{ $ft->id }}" @selected($fuelTypeId==$ft->id)>{{ $ft->name }}</option>
            @endforeach
        </select>
    </div>
</form>

@if($prices->isEmpty())
    <x-empty-state icon="bi-trophy" title="No available stations found" message="All stations may currently be out of this fuel type." />
@else
    @php $cheapest = $prices->first(); @endphp
    <div class="card mb-4" style="border-color:var(--gg-accent); background:var(--gg-accent-light);">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="small fw-bold" style="color:var(--gg-accent);">CHEAPEST NEARBY</div>
                <h5 class="mb-0">{{ $cheapest->station->station_name }} — ₱{{ number_format($cheapest->price,2) }}</h5>
                <div class="text-muted-gg small">{{ $cheapest->station->address }}, {{ $cheapest->station->barangay }}</div>
            </div>
            <a href="{{ route('stations.show', $cheapest->station) }}" class="btn btn-brand">View Station</a>
        </div>
    </div>

    <div class="gg-table gg-table-responsive">
        <table class="table">
            <thead><tr><th>Rank</th><th>Station</th><th>Barangay</th><th class="text-end">Price</th><th>Availability</th><th class="no-print">Action</th></tr></thead>
            <tbody>
            @foreach($prices as $p)
                <tr>
                    <td>#{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $p->station->station_name }}</td>
                    <td class="text-muted-gg small">{{ $p->station->barangay }}</td>
                    <td class="text-end">₱{{ number_format($p->price,2) }}</td>
                    <td><x-availability-badge :status="$p->availability_status" /></td>
                    <td><a href="{{ route('stations.show', $p->station) }}" class="btn btn-sm btn-outline-brand">View</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
