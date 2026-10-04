@extends('layouts.app')
@section('title', $news->title)

@section('content')
<nav class="small text-muted-gg mb-2"><a href="{{ route('news.index') }}">News</a> / {{ $news->title }}</nav>

<div class="card">
    @if($news->image_path)
        <img src="{{ $news->imageUrl() }}" alt="{{ $news->title }}" class="w-100" style="max-height:340px; object-fit:cover; border-radius:14px 14px 0 0;">
    @endif
    <div class="card-body p-4">
        <span class="badge mb-2" style="background:var(--gg-accent-light); color:var(--gg-dark); font-weight:600;">{{ \App\Models\News::CATEGORIES[$news->category] }}</span>
        <h4 class="mb-1">{{ $news->title }}</h4>
        <p class="text-muted-gg small mb-4">{{ optional($news->published_at)->format('F d, Y') }}</p>

        <p style="white-space:pre-line;">{{ $news->content }}</p>

        @if($news->fuelType && ($news->previous_price !== null || $news->current_price !== null))
            <div class="card my-3" style="max-width:360px; background:var(--gg-bg);">
                <div class="card-body">
                    <div class="fw-semibold mb-2">{{ $news->fuelType->name }}</div>
                    @if($news->previous_price !== null)
                        <div class="d-flex justify-content-between small"><span>Previous</span><span>₱{{ number_format($news->previous_price,2) }}</span></div>
                    @endif
                    @if($news->current_price !== null)
                        <div class="d-flex justify-content-between small"><span>Current</span><span>₱{{ number_format($news->current_price,2) }}</span></div>
                    @endif
                    @php $change = $news->priceChange(); @endphp
                    @if($change !== null)
                        <div class="d-flex justify-content-between small fw-bold mt-1" style="color:{{ $change > 0 ? '#B91C1C' : ($change < 0 ? '#15803D' : '#6B7280') }};">
                            <span>Change</span>
                            <span>{{ $change > 0 ? '↑ +' : ($change < 0 ? '↓ ' : '→ ') }}₱{{ number_format(abs($change),2) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="d-flex gap-2 flex-wrap mt-3">
            @if($news->station)
                <a href="{{ route('stations.show', $news->station) }}" class="btn btn-brand btn-sm"><i class="bi bi-shop me-1"></i>View Station</a>
            @endif
            @if($news->related_barangay)
                <a href="{{ route('stations.index', ['barangay' => $news->related_barangay]) }}" class="btn btn-outline-brand btn-sm"><i class="bi bi-map me-1"></i>View {{ $news->related_barangay }} Stations</a>
            @endif
        </div>
    </div>
</div>
@endsection
