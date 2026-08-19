@props(['items', 'active'])
{{-- $items: array of ['route' => name, 'label' => ..., 'icon' => 'bi-...'] --}}
<div class="gg-sidebar">
    <div class="brand"><i class="bi bi-fuel-pump-fill"></i> GeoGas<span style="color:var(--gg-accent);">ManFort</span></div>
    <button class="btn btn-sm w-100 d-md-none mb-2" type="button" style="background:transparent;border:1px solid #444;color:#fff;" onclick="document.getElementById('sidebarLinks').classList.toggle('show')">
        <i class="bi bi-list me-1"></i> Menu
    </button>
    <div class="sidebar-links d-md-flex flex-column" id="sidebarLinks">
        @foreach($items as $item)
            <a href="{{ route($item['route']) }}" class="gg-sidebar-link {{ $active === $item['route'] ? 'active' : '' }}">
                <i class="bi {{ $item['icon'] }}"></i> {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</div>
