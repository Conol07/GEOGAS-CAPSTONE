@props(['icon' => 'bi-bar-chart-fill', 'label', 'value', 'desc' => null])
<div class="gg-stat-card">
    <div class="gg-stat-icon"><i class="bi {{ $icon }}"></i></div>
    <div class="gg-stat-value">{{ $value }}</div>
    <div class="gg-stat-label">{{ $label }}</div>
    @if($desc)
        <div class="gg-stat-desc">{{ $desc }}</div>
    @endif
</div>
