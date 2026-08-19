@php
$sidebarItems = [
    ["route"=>"admin.dashboard","label"=>"Dashboard","icon"=>"bi-speedometer2"],
    ["route"=>"admin.stations.index","label"=>"Stations","icon"=>"bi-shop"],
    ["route"=>"admin.users.index","label"=>"Users","icon"=>"bi-people-fill"],
    ["route"=>"admin.verification.index","label"=>"Price Verification","icon"=>"bi-check2-square"],
    ["route"=>"admin.reports.index","label"=>"Reports","icon"=>"bi-file-earmark-text-fill"],
];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'admin.reports.index'])
@section('title', 'Reports')

@section('dashboard-content')
<h4 class="mb-1">Reports</h4>
<p class="text-muted-gg small mb-3">Generate printable reports for review or capstone documentation.</p>
<div class="row g-3">
    <div class="col-md-6">
        <a href="{{ route('admin.reports.stations') }}" class="card text-decoration-none h-100">
            <div class="card-body p-4">
                <div class="gg-stat-icon mb-3"><i class="bi bi-shop"></i></div>
                <h6 class="mb-1">Station List Report</h6>
                <p class="text-muted-gg small mb-0">All registered stations with current verified prices.</p>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a href="{{ route('admin.reports.price-history') }}" class="card text-decoration-none h-100">
            <div class="card-body p-4">
                <div class="gg-stat-icon mb-3"><i class="bi bi-clock-history"></i></div>
                <h6 class="mb-1">Fuel Price Update History Report</h6>
                <p class="text-muted-gg small mb-0">Full submission and verification history.</p>
            </div>
        </a>
    </div>
</div>
@endsection
