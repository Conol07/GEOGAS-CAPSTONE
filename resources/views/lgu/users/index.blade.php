@php
$sidebarItems = [
    ["route"=>"lgu.dashboard","label"=>"Dashboard","icon"=>"bi-speedometer2"],
    ["route"=>"lgu.stations.index","label"=>"Stations","icon"=>"bi-shop"],
    ["route"=>"lgu.users.index","label"=>"Users","icon"=>"bi-people-fill"],
    ["route"=>"lgu.complaints.index","label"=>"Complaints","icon"=>"bi-flag-fill"],
    ["route"=>"lgu.analytics.index","label"=>"Analytics","icon"=>"bi-graph-up"],
    ["route"=>"lgu.reports.index","label"=>"Reports","icon"=>"bi-file-earmark-text-fill"],
];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.users.index'])
@section('title', 'User Management')

@section('dashboard-content')
<h4 class="mb-1">User Management</h4>
<p class="text-muted-gg small mb-3">Managers are created together with their station. Staff accounts are created by their manager.</p>

<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Station</th><th>Status</th><th class="no-print">Actions</th></tr></thead>
        <tbody>
        @forelse($users as $u)
            <tr>
                <td class="fw-semibold">{{ $u->name }}</td>
                <td class="text-muted-gg small">{{ $u->email }}</td>
                <td>{{ ['lgu_admin'=>'LGU Admin','manager'=>'Manager','staff'=>'Staff'][$u->role] }}</td>
                <td class="small">{{ optional($u->stationAssignment?->station)->station_name ?? '—' }}</td>
                <td><x-status-badge :status="$u->status" /></td>
                <td>
                    @if($u->role !== 'lgu_admin')
                    <form method="POST" action="{{ route('lgu.users.toggle', $u) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-brand">{{ $u->status=='active' ? 'Deactivate' : 'Activate' }}</button>
                    </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-muted-gg text-center">No users found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
