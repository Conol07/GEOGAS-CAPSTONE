@php
$sidebarItems = \App\Support\Nav::lguSidebar();
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.complaints.index'])
@section('title', 'Complaint Details')

@section('dashboard-content')
<nav class="small text-muted-gg mb-2"><a href="{{ route('lgu.complaints.index') }}">Complaints</a> / {{ $complaint->reference_no }}</nav>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <h4 class="mb-0">{{ $complaint->subject }}</h4>
    <x-status-badge :status="$complaint->status" />
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header">Complaint Details</div>
            <div class="card-body">
                <p class="mb-2 small"><strong>Reference:</strong> {{ $complaint->reference_no }}</p>
                <p class="mb-2 small"><strong>Category:</strong> {{ \App\Models\Complaint::CATEGORIES[$complaint->category] }}</p>
                <p class="mb-2 small"><strong>Related Station:</strong> {{ optional($complaint->station)->station_name ?? 'Not specified' }}</p>
                <p class="mb-2 small"><strong>Submitted By:</strong> {{ $complaint->name ?: 'Anonymous' }} @if($complaint->contact) ({{ $complaint->contact }}) @endif</p>
                <p class="mb-2 small"><strong>Date Submitted:</strong> {{ $complaint->created_at->format('M d, Y g:i A') }}</p>
                <hr>
                <p class="mb-0">{{ $complaint->description }}</p>
                <hr>
                <div class="small fw-semibold mb-2"><i class="bi bi-image me-1"></i>Image Evidence</div>
                @if($complaint->photo_path)
                    <a href="{{ route('lgu.complaints.evidence', $complaint) }}" target="_blank" rel="noopener">
                        <img src="{{ route('lgu.complaints.evidence', $complaint) }}" class="img-fluid rounded border" style="max-height:300px;" alt="Complaint evidence">
                    </a>
                    <div class="mt-2"><a href="{{ route('lgu.complaints.evidence', $complaint) }}" target="_blank" rel="noopener" class="small">Open full image <i class="bi bi-box-arrow-up-right"></i></a></div>
                @else
                    <p class="text-muted-gg small mb-0">No image attached</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">Update Status &amp; Response</div>
            <div class="card-body">
                <form method="POST" action="{{ route('lgu.complaints.update', $complaint) }}">
                    @csrf @method('PATCH')
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select mb-3">
                        <option value="pending" @selected($complaint->status=='pending')>Pending</option>
                        <option value="under_review" @selected($complaint->status=='under_review')>Under Review</option>
                        <option value="resolved" @selected($complaint->status=='resolved')>Resolved</option>
                    </select>
                    <label class="form-label">Response / Action Taken</label>
                    <textarea name="lgu_response" class="form-control mb-3" rows="4">{{ old('lgu_response', $complaint->lgu_response) }}</textarea>
                    <button class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Save</button>
                </form>

                <hr>
                <p class="small text-muted-gg mb-1">Reviewed: {{ optional($complaint->reviewed_at)->format('M d, Y g:i A') ?? '—' }}</p>
                <p class="small text-muted-gg mb-0">Resolved: {{ optional($complaint->resolved_at)->format('M d, Y g:i A') ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
