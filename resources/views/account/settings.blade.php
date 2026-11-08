@php
$sidebarItems = auth()->user()->isLguAdmin()
    ? \App\Support\Nav::lguSidebar()
    : \App\Support\Nav::stationSidebar(auth()->user());
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'account.settings.edit'])
@section('title', 'Account Settings')

@section('dashboard-content')
<h4 class="mb-1">Account Settings</h4>
<p class="text-muted-gg small mb-4">Manage your profile information and account security.</p>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-person-circle me-1"></i>Profile Information</div>
            <div class="card-body">
                <form method="POST" action="{{ route('account.profile.update') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Information</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', auth()->user()->phone) }}" placeholder="Phone number">
                    </div>
                    <button class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Save Profile</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-shield-lock-fill me-1"></i>Security</div>
            <div class="card-body">
                <p class="text-muted-gg small mb-3">Update the password used to log in ({{ auth()->user()->email }}).</p>
                <form method="POST" action="{{ route('account.password.update') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" required>
                        <div class="form-text">At least 8 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button class="btn btn-brand"><i class="bi bi-key-fill me-1"></i>Update Password</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-sliders me-1"></i>Preferences</div>
            <div class="card-body">
                <p class="text-muted-gg small mb-0">No configurable preferences yet.</p>
            </div>
        </div>
    </div>
</div>
@endsection
