@extends('layouts.app')
@section('title', 'News & Announcements')

@section('content')
<h4 class="mb-1">News &amp; Announcements</h4>
<p class="text-muted-gg mb-3">Fuel-related updates from the Manolo Fortich LGU, and fuel news from across the Philippines.</p>

<ul class="nav nav-pills mb-4 gap-1">
    @foreach(['' => 'All', 'local' => 'Manolo Fortich', 'national' => 'Philippines'] as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ ($scope ?? '') === (string) $key ? 'active' : '' }}" href="{{ route('news.index', $key ? ['scope' => $key] : []) }}"
               @if(($scope ?? '') === (string) $key) style="background:var(--gg-accent); color:#fff;" @else style="color:var(--gg-dark);" @endif>{{ $label }}</a>
        </li>
    @endforeach
</ul>

@if(($scope ?? '') === 'national')
<p class="small text-muted-gg">Headlines link to the original publisher. For official oil industry information, visit the <a href="https://www.doe.gov.ph" target="_blank" rel="noopener noreferrer">Department of Energy</a>.</p>
@endif

@if($news->isEmpty())
    <x-empty-state icon="bi-megaphone" title="No news yet" message="Check back soon for fuel-related updates." />
@else
<div class="row g-3">
    @foreach($news as $n)
        <div class="col-md-6 col-lg-4">
            <x-news-card :news="$n" />
        </div>
    @endforeach
</div>
<div class="mt-4">{{ $news->withQueryString()->links() }}</div>
@endif
@endsection
