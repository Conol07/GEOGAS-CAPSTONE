@php
$isManager = auth()->user()->isManager();
$sidebarItems = collect([
    ["route"=>"station.dashboard","label"=>"Dashboard","icon"=>"bi-speedometer2","perm"=>"view_dashboard"],
    ["route"=>"station.prices.edit","label"=>"Update Fuel Prices","icon"=>"bi-pencil-square","perm"=>"update_prices"],
    ["route"=>"station.history","label"=>"Price History","icon"=>"bi-clock-history","perm"=>"view_price_history"],
    ["route"=>"station.services.edit","label"=>"Station Services","icon"=>"bi-tools","perm"=>"manage_services"],
    ["route"=>"station.reports.index","label"=>"Reports","icon"=>"bi-file-earmark-text-fill","perm"=>"view_reports"],
])->filter(fn($i) => auth()->user()->hasStationPermission($i["perm"]))->values()->all();
if ($isManager) {
    $sidebarItems[] = ["route"=>"station.staff.index","label"=>"Staff Accounts","icon"=>"bi-people-fill"];
}
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.services.edit'])
@section('title', 'Station Services')

@section('dashboard-content')
<h4 class="mb-1">Station Services &amp; Amenities</h4>
<p class="text-muted-gg small mb-3">Check what's currently available at {{ $station->station_name }}. This is shown to the public.</p>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('station.services.update') }}">
            @csrf
            <div class="row g-3">
                @foreach(\App\Models\StationService::CATALOG as $key => $label)
                    @php $checked = optional($services->get($key))->available; @endphp
                    <div class="col-md-4">
                        <div class="form-check p-3 border rounded-3" style="border-color:var(--gg-border) !important;">
                            <input class="form-check-input" type="checkbox" name="service_{{ $key }}" id="svc_{{ $key }}" value="1" {{ $checked ? 'checked' : '' }}>
                            <label class="form-check-label" for="svc_{{ $key }}">{{ $label }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
            <button class="btn btn-brand mt-4"><i class="bi bi-check-lg me-1"></i>Save Services</button>
        </form>
    </div>
</div>
@endsection
