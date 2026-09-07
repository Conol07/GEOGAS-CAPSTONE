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
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.stations.index'])
@section('title', 'Register Station')

@section('dashboard-content')
<h4 class="mb-1">Register New Station</h4>
<p class="text-muted-gg small mb-3">This also creates the station's manager account in one step.</p>
<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('lgu.stations.store') }}">
            @csrf
            @include('lgu.stations._form')

            <hr class="my-4">
            <label class="form-label mb-1">Station Manager Account</label>
            <p class="text-muted-gg small">The manager will have full control over this station's prices, availability, services, and staff. They log in with their email and password.</p>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Username</label>
                    <input type="text" name="manager_username" class="form-control" value="{{ old('manager_username') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Manager Email (login)</label>
                    <input type="email" name="manager_email" class="form-control" value="{{ old('manager_email') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="manager_password" class="form-control" required>
                </div>
            </div>

            <hr class="my-4">
            <label class="form-label mb-2">Initial Station Services</label>
            <div class="row g-2">
                @foreach(\App\Models\StationService::CATALOG as $key => $label)
                    <div class="col-md-4">
                        <div class="form-check p-2 border rounded-3" style="border-color:var(--gg-border) !important;">
                            <input class="form-check-input" type="checkbox" name="service_{{ $key }}" id="svc_{{ $key }}" value="1">
                            <label class="form-check-label small" for="svc_{{ $key }}">{{ $label }}</label>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Register Station</button>
                <a href="{{ route('lgu.stations.index') }}" class="btn btn-outline-brand">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
