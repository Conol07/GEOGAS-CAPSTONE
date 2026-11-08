@props(['at' => null, 'compact' => false])
@if($at && \Illuminate\Support\Carbon::parse($at)->lt(now()->subHours(config('geogas.stale_hours'))))
    <span class="badge text-bg-warning" title="Last updated {{ \Illuminate\Support\Carbon::parse($at)->diffForHumans() }}"><i class="bi bi-exclamation-triangle-fill"></i>@unless($compact) Price may be outdated @endunless</span>
@endif
