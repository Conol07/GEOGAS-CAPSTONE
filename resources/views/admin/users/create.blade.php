@php
$sidebarItems = [
    ["route"=>"admin.dashboard","label"=>"Dashboard","icon"=>"bi-speedometer2"],
    ["route"=>"admin.stations.index","label"=>"Stations","icon"=>"bi-shop"],
    ["route"=>"admin.users.index","label"=>"Users","icon"=>"bi-people-fill"],
    ["route"=>"admin.verification.index","label"=>"Price Verification","icon"=>"bi-check2-square"],
    ["route"=>"admin.reports.index","label"=>"Reports","icon"=>"bi-file-earmark-text-fill"],
];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'admin.users.index'])
@section('title', 'Add User')

@section('dashboard-content')
<h4 class="mb-1">Add User Account</h4>
<p class="text-muted-gg small mb-3">Create an administrator or station personnel account.</p>
<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role" id="roleSelect" class="form-select" onchange="document.getElementById('stationField').classList.toggle('d-none', this.value!=='station_personnel')">
                        <option value="station_personnel" @selected(old('role')=='station_personnel')>Station Personnel</option>
                        <option value="admin" @selected(old('role')=='admin')>Administrator</option>
                    </select>
                </div>
                <div class="col-md-6" id="stationField">
                    <label class="form-label">Assigned Station</label>
                    <select name="station_id" class="form-select">
                        <option value="">— Select Station —</option>
                        @foreach($stations as $s)
                            <option value="{{ $s->id }}" @selected(old('station_id')==$s->id)>{{ $s->station_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Create Account</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-brand">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
