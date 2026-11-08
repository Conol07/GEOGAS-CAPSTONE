@php
$sidebarItems = \App\Support\Nav::lguSidebar();
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.session-logs.index'])
@section('title', 'Session Logs')

@section('dashboard-content')
<h4 class="mb-1">Session Logs</h4>
<p class="text-muted-gg small mb-3">Login activity across LGU, Station Manager, and Staff accounts.</p>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="role" class="form-select" onchange="this.form.submit()">
            <option value="">All Roles</option>
            <option value="lgu_admin" @selected(request('role')=='lgu_admin')>LGU Admin</option>
            <option value="manager" @selected(request('role')=='manager')>Manager</option>
            <option value="staff" @selected(request('role')=='staff')>Staff</option>
        </select>
    </div>
    <div class="col-md-3">
        <select name="action" class="form-select" onchange="this.form.submit()">
            <option value="">All Actions</option>
            <option value="login" @selected(request('action')=='login')>Login</option>
            <option value="logout" @selected(request('action')=='logout')>Logout</option>
            <option value="failed_login" @selected(request('action')=='failed_login')>Failed Login</option>
            <option value="account_settings_changed" @selected(request('action')=='account_settings_changed')>Account Settings Changed</option>
            <option value="important_action" @selected(request('action')=='important_action')>Important Action</option>
        </select>
    </div>
    <div class="col-md-4">
        <select name="user_id" class="form-select" onchange="this.form.submit()">
            <option value="">All Users</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->name }} ({{ $u->role }})</option>
            @endforeach
        </select>
    </div>
</form>

@if($logs->isEmpty())
    <x-empty-state icon="bi-clock-history" title="No session activity found" />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>User</th><th>Role</th><th>Action</th><th>Description</th><th>Date/Time</th><th>IP</th><th>Device</th></tr></thead>
        <tbody>
        @foreach($logs as $log)
            <tr>
                <td class="fw-semibold small">{{ $log->user->name ?? $log->email_attempted ?? 'Unknown' }}</td>
                <td class="small">{{ $log->role ?? '—' }}</td>
                <td><span class="status-badge {{ $log->action == 'failed_login' ? 'status-rejected' : ($log->action=='login' ? 'status-approved' : 'status-pending') }}">{{ ucwords(str_replace('_',' ',$log->action)) }}</span></td>
                <td class="small text-muted-gg">{{ $log->description ?? '—' }}</td>
                <td class="small text-muted-gg">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                <td class="small text-muted-gg">{{ $log->ip_address ?? '—' }}</td>
                <td class="small text-muted-gg">{{ $log->device() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $logs->links() }}</div>
@endif
@endsection
