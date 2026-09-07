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
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.reports.index'])
@section('title', 'Complaint Report')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Complaint Report</h4>
        <p class="text-muted-gg small mb-0">Generated {{ now()->format('F d, Y g:i A') }}</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('lgu.reports.complaints', array_merge(request()->query(), ['export'=>'csv'])) }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
        <button class="btn btn-outline-brand btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
    </div>
</div>

<form method="GET" class="row g-2 mb-3 no-print">
    <div class="col-md-3">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="pending" @selected(request('status')=='pending')>Pending</option>
            <option value="under_review" @selected(request('status')=='under_review')>Under Review</option>
            <option value="resolved" @selected(request('status')=='resolved')>Resolved</option>
        </select>
    </div>
</form>

<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Reference</th><th>Category</th><th>Station</th><th>Area</th><th>Submitted</th><th>Status</th><th>Resolution</th></tr></thead>
        <tbody>
        @foreach($complaints as $c)
            <tr>
                <td class="small">{{ $c->reference_no }}</td>
                <td class="small">{{ \App\Models\Complaint::CATEGORIES[$c->category] }}</td>
                <td class="small">{{ optional($c->station)->station_name ?? 'General' }}</td>
                <td class="small">{{ optional($c->station)->barangay ?? '—' }}</td>
                <td class="text-muted-gg small">{{ $c->created_at->format('M d, Y') }}</td>
                <td><x-status-badge :status="$c->status" /></td>
                <td class="small">{{ $c->lgu_response ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
