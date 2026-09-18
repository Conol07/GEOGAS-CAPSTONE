@php
$sidebarItems = \App\Support\Nav::stationSidebar(auth()->user());
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.staff.index'])
@section('title', 'Add Staff')

@section('dashboard-content')
<h4 class="mb-1">Add Staff Account</h4>
<p class="text-muted-gg small mb-3">Choose exactly what this staff member can access.</p>

<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('station.staff.store') }}">
            @csrf
            <div class="row g-3 mb-3">
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
            </div>

            <label class="form-label">Permissions</label>
            <div class="row g-2 mb-4">
                @foreach($permissions as $key => $label)
                    <div class="col-md-4">
                        <div class="form-check p-2 border rounded-3" style="border-color:var(--gg-border) !important;">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $key }}" id="perm_{{ $key }}" {{ in_array($key, old('permissions', ['view_dashboard'])) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="perm_{{ $key }}">{{ $label }}</label>
                        </div>
                    </div>
                @endforeach
            </div>

            <button class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Create Staff Account</button>
            <a href="{{ route('station.staff.index') }}" class="btn btn-outline-brand">Cancel</a>
        </form>
    </div>
</div>
@endsection
