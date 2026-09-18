@php
$sidebarItems = \App\Support\Nav::lguSidebar();
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.stations.index'])
@section('title', 'Edit Station')

@section('dashboard-content')
<h4 class="mb-1">Edit Station</h4>
<p class="text-muted-gg small mb-3">Update {{ $station->station_name }}'s information and location.</p>
<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('lgu.stations.update', $station) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('lgu.stations._form')
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Update Station</button>
                <a href="{{ route('lgu.stations.index') }}" class="btn btn-outline-brand">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
