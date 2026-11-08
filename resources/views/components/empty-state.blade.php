@props(['icon' => 'bi-inbox', 'title', 'message' => null])
<div class="gg-empty">
    <i class="bi {{ $icon }}"></i>
    <h5>{{ $title }}</h5>
    @if($message)
        <p class="mb-3">{{ $message }}</p>
    @endif
    {{ $slot }}
</div>
