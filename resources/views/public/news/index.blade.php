@extends('layouts.app')
@section('title', 'News & Announcements')

@section('content')
<h4 class="mb-1">News &amp; Announcements</h4>
<p class="text-muted-gg mb-4">Official fuel-related updates from the Manolo Fortich LGU.</p>

@if($news->isEmpty())
    <x-empty-state icon="bi-megaphone" title="No announcements yet" message="Check back soon for fuel-related updates." />
@else
<div class="row g-3">
    @foreach($news as $n)
        <div class="col-md-6 col-lg-4">
            <x-news-card :news="$n" />
        </div>
    @endforeach
</div>
<div class="mt-4">{{ $news->links() }}</div>
@endif
@endsection
