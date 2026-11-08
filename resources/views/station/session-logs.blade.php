@php
$sidebarItems = \App\Support\Nav::stationSidebar(auth()->user());
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.session-logs.index'])
@section('title', 'Session Logs')

@section('dashboard-content')
<h4 class="mb-1">Session Logs</h4>
<p class="text-muted-gg small mb-3">
    @if(auth()->user()->isManager())
        Your login activity and your station's staff login activity.
    @else
        Your own login activity.
    @endif
</p>

@if(auth()->user()->isManager() && $staffOptions->isNotEmpty())
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <select name="user_id" class="form-select" onchange="this.form.submit()">
            <option value="">All (You + Staff)</option>
            @foreach($staffOptions as $u)
                <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->name }} ({{ $u->role }})</option>
            @endforeach
        </select>
    </div>
</form>
@endif

@if($logs->isEmpty())
    <x-empty-state icon="bi-clock-history" title="No session activity found" />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>User</th><th>Role</th><th>Action</th><th>Description</th><th>Date/Time</th><th>Device</th></tr></thead>
        <tbody>
        @foreach($logs as $log)
            <tr>
                <td class="fw-semibold small">{{ $log->user->name ?? 'Unknown' }}</td>
                <td class="small">{{ $log->role ?? '—' }}</td>
                <td><span class="status-badge {{ $log->action == 'failed_login' ? 'status-rejected' : ($log->action=='login' ? 'status-approved' : 'status-pending') }}">{{ ucwords(str_replace('_',' ',$log->action)) }}</span></td>
                <td class="small text-muted-gg">{{ $log->description ?? '—' }}</td>
                <td class="small text-muted-gg">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                <td class="small text-muted-gg">{{ $log->device() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $logs->links() }}</div>
@endif
@endsection
