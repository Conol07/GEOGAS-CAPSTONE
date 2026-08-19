@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="gg-login-shell">
            <div class="gg-login-brand">
                <i class="bi bi-fuel-pump-fill"></i>
                <h3 class="text-white mb-2">GeoGas ManFort</h3>
                <p class="mb-0" style="color:#D1D5DB;">Fuel Price Monitoring &amp; Geospatial Analysis System for Manolo Fortich, Bukidnon.</p>
            </div>
            <div class="gg-login-form">
                <h4 class="mb-1">Station / Admin Login</h4>
                <p class="text-muted-gg small mb-4">Sign in to manage your station or the system.</p>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                    <button type="submit" class="btn btn-brand w-100">Log In</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
