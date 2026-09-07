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
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.staff.index'])
@section('title', 'Edit Staff Permissions')

@section('dashboard-content')
<h4 class="mb-1">Edit Permissions — {{ $staffUser->name }}</h4>
<p class="text-muted-gg small mb-3">{{ $staffUser->email }}</p>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('station.staff.update', $staffUser) }}">
            @csrf @method('PATCH')
            <div class="row g-2 mb-4">
                @foreach($permissions as $key => $label)
                    <div class="col-md-4">
                        <div class="form-check p-2 border rounded-3" style="border-color:var(--gg-border) !important;">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $key }}" id="perm_{{ $key }}" {{ in_array($key, old('permissions', $assignment->permissions ?? [])) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="perm_{{ $key }}">{{ $label }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
            <button class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Save Permissions</button>
            <a href="{{ route('station.staff.index') }}" class="btn btn-outline-brand">Cancel</a>
        </form>
    </div>
</div>
@endsection
