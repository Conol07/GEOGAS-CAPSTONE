@php
$sidebarItems = \App\Support\Nav::lguSidebar();
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.gis.index'])
@section('title', 'GIS Map / Heatmap')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <h4 class="mb-1">GIS Fuel Price Map</h4>
        <p class="text-muted-gg small mb-0">Geographic distribution of current fuel prices across {{ $stationCount }} active station{{ $stationCount === 1 ? '' : 's' }} in Manolo Fortich. Uses recorded prices only; no forecasting.</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('lgu.analytics.index') }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-graph-up"></i> Price Trends</a>
        <a href="{{ route('lgu.stations.index') }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-shop"></i> Stations</a>
        <button type="button" class="btn btn-brand btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>
<div class="d-none d-print-block mb-2"><strong>GeoGas ManFort</strong> &mdash; GIS Fuel Price Report &middot; Generated {{ now()->format('M d, Y g:i A') }}</div>

<div class="card mb-3 no-print">
    <div class="card-header"><i class="bi bi-toggles me-1"></i>Public Heatmap Settings</div>
    <div class="card-body">
        <form method="POST" action="{{ route('lgu.gis.settings') }}" class="row g-3 align-items-center">
            @csrf @method('PATCH')
            <div class="col-md-5">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="heatmap_enabled" id="setHeatEnabled" value="1" @checked($heatmapEnabled)>
                    <label class="form-check-label fw-semibold" for="setHeatEnabled">Heatmap available to the public</label>
                </div>
                <div class="text-muted-gg small">OFF removes the heatmap switch from the public map. You can always use it here.</div>
            </div>
            <div class="col-md-5">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="heatmap_auto" id="setHeatAuto" value="1" @checked($heatmapAuto)>
                    <label class="form-check-label fw-semibold" for="setHeatAuto">Automatic heatmap</label>
                </div>
                <div class="text-muted-gg small">ON: the map opens with the heatmap already drawn from live prices. OFF: visitors switch it on themselves.</div>
            </div>
            <div class="col-md-2 text-md-end"><button class="btn btn-brand btn-sm">Save</button></div>
        </form>
    </div>
</div>

<x-gis-console instance-id="lgugis" :auto-init="true" height="clamp(340px, 65vh, 620px)" :heatmap-available="true" :heat-default="true" />
@endsection
