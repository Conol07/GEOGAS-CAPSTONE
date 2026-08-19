@php
$sidebarItems = [
    ["route"=>"station.dashboard","label"=>"Dashboard","icon"=>"bi-speedometer2"],
    ["route"=>"station.prices.create","label"=>"Update Fuel Prices","icon"=>"bi-pencil-square"],
    ["route"=>"station.history","label"=>"Update History","icon"=>"bi-clock-history"],
];
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'station.history'])
@section('title', 'Update History')

@section('dashboard-content')
<h4 class="mb-1">My Update History</h4>
<p class="text-muted-gg small mb-3">All fuel price submissions and their verification status.</p>

@if($updates->isEmpty())
    <x-empty-state icon="bi-clock-history" title="No submissions yet" message="Prices you submit will show up here with their verification status." />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Date</th><th>Gasoline</th><th>Diesel</th><th>Status</th><th>Reason (if rejected)</th></tr></thead>
        <tbody>
        @foreach($updates as $u)
            <tr>
                <td>{{ $u->effective_date->format('M d, Y') }}</td>
                <td>₱{{ number_format($u->gasoline_price,2) }}</td>
                <td>₱{{ number_format($u->diesel_price,2) }}</td>
                <td><x-status-badge :status="$u->status" /></td>
                <td class="text-muted-gg small">{{ $u->rejection_reason }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $updates->links() }}</div>
@endif
@endsection
