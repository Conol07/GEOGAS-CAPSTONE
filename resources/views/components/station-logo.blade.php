@props(['station', 'size' => 44])
@php $url = $station->logoUrl(); @endphp
@if($url)
    <img src="{{ $url }}" alt="{{ $station->station_name }} logo" class="gg-station-logo" style="width:{{ $size }}px; height:{{ $size }}px;">
@else
    <div class="gg-station-logo gg-station-logo-placeholder" style="width:{{ $size }}px; height:{{ $size }}px; font-size:{{ round($size * 0.45) }}px;">
        <i class="bi bi-fuel-pump-fill"></i>
    </div>
@endif
