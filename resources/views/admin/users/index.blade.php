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
@section('title', 'User Management')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">User Management</h4>
        <p class="text-muted-gg small mb-0">Manage administrator and station personnel accounts.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-brand"><i class="bi bi-person-plus-fill me-1"></i>Add User</a>
</div>

@if($users->isEmpty())
    <x-empty-state icon="bi-people" title="No users found" message="Add a station personnel or administrator account to get started." />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Station</th><th>Status</th><th class="no-print">Actions</th></tr></thead>
        <tbody>
        @foreach($users as $u)
            <tr>
                <td class="fw-semibold">{{ $u->name }}</td>
                <td class="text-muted-gg">{{ $u->email }}</td>
                <td>{{ $u->role === 'admin' ? 'Administrator' : 'Station Personnel' }}</td>
                <td>{{ optional($u->stationAssignment?->station)->station_name ?? '—' }}</td>
                <td><x-status-badge :status="$u->status" /></td>
                <td>
                    <form method="POST" action="{{ route('admin.users.toggle', $u) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-brand">{{ $u->status=='active' ? 'Deactivate' : 'Activate' }}</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $users->links() }}</div>
@endif
@endsection
