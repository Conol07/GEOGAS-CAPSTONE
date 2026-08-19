@php
$sidebarItems = [
    ["route"=>"admin.dashboard","label"=>"Dashboard","icon"=>"bi-speedometer2"],
    ["route"=>"admin.stations.index","label"=>"Stations","icon"=>"bi-shop"],
    ["route"=>"admin.users.index","label"=>"Users","icon"=>"bi-people-fill"],
    ["route"=>"admin.verification.index","label"=>"Price Verification","icon"=>"bi-check2-square"],
    ["route"=>"admin.reports.index","label"=>"Reports","icon"=>"bi-file-earmark-text-fill"],
];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'admin.stations.index'])
@section('title', 'Edit Station')

@section('dashboard-content')
<h4 class="mb-1">Edit Station</h4>
<p class="text-muted-gg small mb-3">Update {{ $station->station_name }}'s information.</p>
<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.stations.update', $station) }}">
            @csrf @method('PUT')
            @include('admin.stations._form')
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Update Station</button>
                <a href="{{ route('admin.stations.index') }}" class="btn btn-outline-brand">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
