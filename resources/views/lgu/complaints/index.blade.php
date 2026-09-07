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
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.complaints.index'])
@section('title', 'Complaints Management')

@section('dashboard-content')
<h4 class="mb-1">Complaints Management</h4>
<p class="text-muted-gg small mb-3">Review, search, and act on public complaints.</p>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <input type="text" name="q" class="form-control" placeholder="Search reference, subject, name" value="{{ request('q') }}">
    </div>
    <div class="col-md-2">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="pending" @selected(request('status')=='pending')>Pending</option>
            <option value="under_review" @selected(request('status')=='under_review')>Under Review</option>
            <option value="resolved" @selected(request('status')=='resolved')>Resolved</option>
        </select>
    </div>
    <div class="col-md-3">
        <select name="category" class="form-select" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach(\App\Models\Complaint::CATEGORIES as $key => $label)
                <option value="{{ $key }}" @selected(request('category')==$key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="station_id" class="form-select" onchange="this.form.submit()">
            <option value="">All Stations</option>
            @foreach($stations as $s)
                <option value="{{ $s->id }}" @selected(request('station_id')==$s->id)>{{ $s->station_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-1"><button class="btn btn-brand w-100"><i class="bi bi-search"></i></button></div>
</form>

@if($complaints->isEmpty())
    <x-empty-state icon="bi-flag" title="No complaints found" message="Adjust your filters, or check back later." />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Reference</th><th>Category</th><th>Station</th><th>Submitted</th><th>Status</th><th class="no-print">Actions</th></tr></thead>
        <tbody>
        @foreach($complaints as $c)
            <tr>
                <td class="small">{{ $c->reference_no }}</td>
                <td class="small">{{ \App\Models\Complaint::CATEGORIES[$c->category] }}</td>
                <td class="small">{{ optional($c->station)->station_name ?? 'General' }}</td>
                <td class="text-muted-gg small">{{ $c->created_at->format('M d, Y') }}</td>
                <td><x-status-badge :status="$c->status" /></td>
                <td><a href="{{ route('lgu.complaints.show', $c) }}" class="btn btn-sm btn-outline-brand">View</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $complaints->links() }}</div>
@endif
@endsection
