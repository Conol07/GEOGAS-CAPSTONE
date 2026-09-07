@php
$sidebarItems = [
    ["route"=>"lgu.dashboard","label"=>"Dashboard","icon"=>"bi-speedometer2"],
    ["route"=>"lgu.stations.index","label"=>"Stations","icon"=>"bi-shop"],
    ["route"=>"lgu.users.index","label"=>"Users","icon"=>"bi-people-fill"],
    ["route"=>"lgu.complaints.index","label"=>"Complaints","icon"=>"bi-flag-fill"],
    ["route"=>"lgu.analytics.index","label"=>"Analytics","icon"=>"bi-graph-up"],
    ["route"=>"lgu.reports.index","label"=>"Reports","icon"=>"bi-file-earmark-text-fill"],
];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.analytics.index'])
@section('title', 'Analytics')

@section('dashboard-content')
<h4 class="mb-1">Analytics</h4>
<p class="text-muted-gg small mb-3">Fuel price, station, and complaint analytics — computed from live data.</p>

<form method="GET" class="row g-2 mb-4">
    <div class="col-md-3">
        <select name="fuel_type_id" class="form-select">
            <option value="">All Fuel Types</option>
            @foreach($fuelTypes as $ft)
                <option value="{{ $ft->id }}" @selected(request('fuel_type_id')==$ft->id)>{{ $ft->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="barangay" class="form-select">
            <option value="">All Barangays</option>
            @foreach($barangays as $b)
                <option value="{{ $b }}" @selected(request('barangay')==$b)>{{ $b }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <input type="date" name="from" class="form-control" value="{{ request('from') }}" placeholder="From">
    </div>
    <div class="col-md-2">
        <input type="date" name="to" class="form-control" value="{{ request('to') }}" placeholder="To">
    </div>
    <div class="col-md-2"><button class="btn btn-brand w-100">Filter</button></div>
</form>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Average Price by Fuel Type</div>
            <div class="card-body"><canvas id="priceByFuelChart" height="200"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Station Availability Breakdown</div>
            <div class="card-body"><canvas id="availabilityChart" height="200"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Average Price by Barangay</div>
            <div class="card-body"><canvas id="priceByAreaChart" height="220"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Complaints by Category</div>
            <div class="card-body"><canvas id="complaintsChart" height="220"></canvas></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">30-Day Average Price Trend</div>
    <div class="card-body"><canvas id="trendChart" height="120"></canvas></div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <h6 class="mb-2">Stations by Barangay</h6>
        <div class="gg-table gg-table-responsive">
            <table class="table">
                <thead><tr><th>Barangay</th><th>Stations</th></tr></thead>
                <tbody>
                @foreach($stationsByBarangay as $barangay => $count)
                    <tr><td>{{ $barangay }}</td><td>{{ $count }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-6">
        <h6 class="mb-2">Price Range by Fuel Type</h6>
        <div class="gg-table gg-table-responsive">
            <table class="table">
                <thead><tr><th>Fuel Type</th><th>Lowest</th><th>Average</th><th>Highest</th></tr></thead>
                <tbody>
                @foreach($priceByFuelType as $name => $row)
                    <tr>
                        <td>{{ $name }}</td>
                        <td>₱{{ number_format($row['lowest'],2) }}</td>
                        <td>₱{{ number_format($row['average'],2) }}</td>
                        <td>₱{{ number_format($row['highest'],2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const orange = '#F58220', black = '#181818', green = '#15803D', amber = '#B45309', red = '#B91C1C', gray = '#6B7280';

    new Chart(document.getElementById('priceByFuelChart'), {
        type: 'bar',
        data: {
            labels: @json($priceByFuelType->keys()),
            datasets: [{ label: 'Average Price (₱)', data: @json($priceByFuelType->pluck('average')->values()), backgroundColor: orange }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('availabilityChart'), {
        type: 'doughnut',
        data: {
            labels: ['Has Enough', 'Almost Empty', 'No Fuel', 'No Data'],
            datasets: [{ data: [
                {{ $availabilityCounts['enough'] }}, {{ $availabilityCounts['almost_empty'] }},
                {{ $availabilityCounts['no_fuel'] }}, {{ $availabilityCounts['unknown'] }}
            ], backgroundColor: [green, amber, red, gray] }]
        }
    });

    new Chart(document.getElementById('priceByAreaChart'), {
        type: 'bar',
        data: {
            labels: @json($priceByBarangay->keys()),
            datasets: [{ label: 'Average Price (₱)', data: @json($priceByBarangay->pluck('average')->values()), backgroundColor: black }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('complaintsChart'), {
        type: 'pie',
        data: {
            labels: @json($complaintsByCategory->keys()),
            datasets: [{ data: @json($complaintsByCategory->values()), backgroundColor: [orange, black, green, amber, red, gray] }]
        }
    });

    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: @json($trend->pluck('day')),
            datasets: [{ label: 'Average Price (₱)', data: @json($trend->pluck('avg_price')), borderColor: orange, backgroundColor: 'rgba(245,130,32,0.15)', fill: true, tension: 0.3 }]
        },
        options: { plugins: { legend: { display: false } } }
    });
</script>
@endpush
@endsection
