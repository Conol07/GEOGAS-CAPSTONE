@props(['status'])
@php
    $map = [
        'enough' => ['label' => 'Has Enough Gasoline', 'dot' => '#15803D', 'bg' => '#ECFDF5', 'text' => '#15803D', 'icon' => 'bi-check-circle-fill'],
        'almost_empty' => ['label' => 'Almost Empty', 'dot' => '#B45309', 'bg' => '#FFFBEB', 'text' => '#B45309', 'icon' => 'bi-exclamation-triangle-fill'],
        'no_fuel' => ['label' => 'No Gasoline', 'dot' => '#B91C1C', 'bg' => '#FEF2F2', 'text' => '#B91C1C', 'icon' => 'bi-x-circle-fill'],
        'unknown' => ['label' => 'No Data Yet', 'dot' => '#6B7280', 'bg' => '#F3F4F6', 'text' => '#6B7280', 'icon' => 'bi-question-circle-fill'],
    ];
    $s = $map[$status] ?? $map['unknown'];
@endphp
<span class="status-badge" style="background:{{ $s['bg'] }}; color:{{ $s['text'] }};">
    <i class="bi {{ $s['icon'] }}"></i> {{ $s['label'] }}
</span>
