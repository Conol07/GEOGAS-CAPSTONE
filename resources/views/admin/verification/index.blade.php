@php
$sidebarItems = [
    ['route'=>'admin.dashboard','label'=>'Dashboard','icon'=>'bi-speedometer2'],
    ['route'=>'admin.stations.index','label'=>'Stations','icon'=>'bi-shop'],
    ['route'=>'admin.users.index','label'=>'Users','icon'=>'bi-people-fill'],
    ['route'=>'admin.verification.index','label'=>'Price Verification','icon'=>'bi-check2-square'],
    ['route'=>'admin.reports.index','label'=>'Reports','icon'=>'bi-file-earmark-text-fill'],
];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'admin.verification.index'])
@section('title', 'Fuel Price Verification')

@section('dashboard-content')
<h4 class="mb-1">Fuel Price Verification</h4>
<p class="text-muted-gg small mb-3">Review submissions from station personnel before they go live to the public.</p>

<ul class="nav nav-pills mb-3 gap-2">
    @foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','all'=>'All'] as $key=>$label)
        <li class="nav-item">
            <a class="btn btn-sm {{ $status==$key ? 'btn-brand' : 'btn-outline-brand' }}" href="{{ route('admin.verification.index', ['status'=>$key]) }}">{{ $label }}</a>
        </li>
    @endforeach
</ul>

@if($updates->isEmpty())
    <x-empty-state icon="bi-check2-circle" title="All caught up"
        message="There are currently no fuel price updates in this view." />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Station</th><th>Submitted By</th><th>Prices</th><th>Submitted</th><th>Status</th><th class="no-print">Actions</th></tr></thead>
        <tbody>
        @foreach($updates as $u)
            <tr>
                <td class="fw-semibold">{{ $u->station->station_name }}</td>
                <td>{{ $u->submitter->name }}</td>
                <td class="small">Gas ₱{{ number_format($u->gasoline_price,2) }} / Diesel ₱{{ number_format($u->diesel_price,2) }}</td>
                <td class="small text-muted-gg">{{ $u->created_at->format('M d, Y g:i A') }}</td>
                <td><x-status-badge :status="$u->status" /></td>
                <td>
                    @if($u->status === 'pending')
                        <div class="d-flex gap-1">
                            <form method="POST" action="{{ route('admin.verification.approve', $u) }}" onsubmit="return confirm('Approve this price update?');">
                                @csrf
                                <button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Approve</button>
                            </form>
                            <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#reject{{ $u->id }}"><i class="bi bi-x-lg"></i> Reject</button>
                        </div>

                        <div class="modal fade" id="reject{{ $u->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="POST" action="{{ route('admin.verification.reject', $u) }}" class="modal-content">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reject Fuel Price Update</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label">Reason (optional)</label>
                                        <textarea name="rejection_reason" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-brand" data-bs-dismiss="modal">Cancel</button>
                                        <button class="btn btn-brand" onclick="return confirm('Reject this price update?');">Confirm Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <span class="text-muted-gg small">Verified {{ optional($u->verified_at)->format('M d, Y') }}</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $updates->links() }}</div>
@endif
@endsection
