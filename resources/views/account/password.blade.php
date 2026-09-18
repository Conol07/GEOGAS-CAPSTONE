@php
$sidebarItems = auth()->user()->isLguAdmin()
    ? \App\Support\Nav::lguSidebar()
    : \App\Support\Nav::stationSidebar(auth()->user());
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'account.password.edit'])
@section('title', 'Change Password')

@section('dashboard-content')
<h4 class="mb-1">Change Password</h4>
<p class="text-muted-gg small mb-3">Update the password for your own account ({{ auth()->user()->email }}).</p>

<div class="card" style="max-width:480px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('account.password.update') }}">
            @csrf
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
@endsection
