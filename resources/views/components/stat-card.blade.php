@props(['icon' => 'bi-bar-chart-fill', 'label', 'value', 'desc' => null, 'href' => null])
@if($href)<a href="{{ $href }}" class="gg-stat-card gg-stat-link text-decoration-none d-block" title="Open {{ $label }}">@else<div class="gg-stat-card">@endif
    <div class="gg-stat-icon"><i class="bi {{ $icon }}"></i></div>
    <div class="gg-stat-value">{{ $value }}</div>
    <div class="gg-stat-label">{{ $label }}</div>
    @if($desc)
        <div class="gg-stat-desc">{{ $desc }}</div>
    @endif
    @if($href)<span class="gg-stat-go"><i class="bi bi-arrow-right-circle-fill"></i></span>@endif
@if($href)</a>@else</div>@endif
