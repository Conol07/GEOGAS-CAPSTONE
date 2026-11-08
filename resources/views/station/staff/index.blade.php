@php
$sidebarItems = \App\Support\Nav::stationSidebar(auth()->user());
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.staff.index'])
@section('title', 'Staff Accounts')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Staff Accounts</h4>
        <p class="text-muted-gg small mb-0">Accounts you create can only ever belong to your station.</p>
    </div>
    <a href="{{ route('station.staff.create') }}" class="btn btn-brand"><i class="bi bi-person-plus-fill me-1"></i>Add Staff</a>
</div>

@if($staff->isEmpty())
    <x-empty-state icon="bi-people" title="No staff accounts yet" message="Add a staff account to delegate day-to-day updates." />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Permissions</th><th>Status</th><th class="no-print">Actions</th></tr></thead>
        <tbody>
        @foreach($staff as $assignment)
            <tr>
                <td class="fw-semibold">{{ $assignment->user->name }}</td>
                <td class="text-muted-gg small">{{ $assignment->user->email }}</td>
                <td class="small">{{ count($assignment->permissions ?? []) }} permission(s)</td>
                <td><x-status-badge :status="$assignment->user->status" /></td>
                <td>
                    <div class="d-flex gap-1 flex-wrap">
                        <a href="{{ route('station.staff.edit', $assignment->user) }}" class="btn btn-sm btn-outline-brand">Edit</a>
                        <form method="POST" action="{{ route('station.staff.toggle', $assignment->user) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-outline-brand">{{ $assignment->user->status=='active' ? 'Deactivate' : 'Activate' }}</button>
                        </form>
                        <form method="POST" action="{{ route('station.staff.reset-password', $assignment->user) }}" onsubmit="return confirm('Reset this staff member\'s password?');">
                            @csrf
                            <button class="btn btn-sm btn-outline-brand">Reset Password</button>
                        </form>
                        <form method="POST" action="{{ route('station.staff.destroy', $assignment->user) }}" onsubmit="return confirm('Delete this staff account? This cannot be undone.');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
