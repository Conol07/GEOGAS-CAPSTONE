@extends('layouts.app')
@section('title', 'Complaint Submitted')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body p-5 text-center">
                <div class="gg-stat-icon mx-auto mb-3" style="width:56px; height:56px; font-size:1.5rem;"><i class="bi bi-check-circle-fill"></i></div>
                <h4 class="mb-2">Complaint Submitted</h4>
                <p class="text-muted-gg mb-4">Thank you for helping us keep GeoGas ManFort accurate. Your report has been sent to the LGU.</p>
                <div class="p-3 mb-4" style="background:var(--gg-bg); border-radius:12px;">
                    <div class="small text-muted-gg">Reference Number</div>
                    <div class="h4 mb-0">{{ $complaint->reference_no }}</div>
                </div>
                <p class="text-muted-gg small">Keep this reference number for your records.</p>
                <a href="{{ route('home') }}" class="btn btn-outline-brand">Back to Home</a>
            </div>
        </div>
    </div>
</div>
@endsection
