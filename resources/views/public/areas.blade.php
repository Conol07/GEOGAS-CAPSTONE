@extends('layouts.app')
@section('title', 'Price by Area')

@section('content')
<h4 class="mb-1">Fuel Price by Barangay</h4>
<p class="text-muted-gg mb-4">Average, lowest, and highest prices per barangay in Manolo Fortich.</p>

<form method="GET" class="row g-2 mb-4">
    <div class="col-md-3">
        <select name="fuel_type_id" class="form-select" onchange="this.form.submit()">
            @foreach($fuelTypes as $ft)
                <option value="{{ $ft->id }}" @selected($fuelTypeId==$ft->id)>{{ $ft->name }}</option>
            @endforeach
        </select>
    </div>
</form>

@if($areas->isEmpty())
    <x-empty-state icon="bi-map" title="No area data yet" message="Check back once stations report prices for this fuel type." />
@else
<div class="row g-3">
    @foreach($areas as $area)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="mb-1"><i class="bi bi-geo-alt-fill me-1" style="color:var(--gg-accent);"></i>{{ $area['barangay'] }}</h6>
                    <p class="text-muted-gg small mb-3">{{ $area['station_count'] }} station(s) reporting</p>
                    <div class="row g-2 text-center mb-2">
                        <div class="col-4">
                            <div class="small text-muted-gg">Lowest</div>
                            <div class="fw-bold">₱{{ number_format($area['lowest'],2) }}</div>
                        </div>
                        <div class="col-4">
                            <div class="small text-muted-gg">Average</div>
                            <div class="fw-bold">₱{{ number_format($area['average'],2) }}</div>
                        </div>
                        <div class="col-4">
                            <div class="small text-muted-gg">Highest</div>
                            <div class="fw-bold">₱{{ number_format($area['highest'],2) }}</div>
                        </div>
                    </div>
                    {{-- simple visual price-range bar --}}
                    @php
                        $min = $areas->min('lowest'); $max = $areas->max('highest');
                        $range = max($max - $min, 0.01);
                        $left = (($area['lowest'] - $min) / $range) * 100;
                        $width = max(((($area['highest'] - $area['lowest']) / $range) * 100), 3);
                    @endphp
                    <div style="height:8px; background:#F3F4F6; border-radius:99px; position:relative; margin-top:.75rem;">
                        <div style="position:absolute; left:{{ $left }}%; width:{{ $width }}%; height:8px; background:var(--gg-accent); border-radius:99px;"></div>
                    </div>
                    <p class="text-muted-gg small mt-2 mb-0">Range: ₱{{ number_format($area['lowest'],2) }} – ₱{{ number_format($area['highest'],2) }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
@endsection
