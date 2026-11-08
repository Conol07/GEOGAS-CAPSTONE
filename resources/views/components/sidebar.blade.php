@props(['items', 'active'])
{{-- $items: grouped nav from App\Support\Nav — [['label','icon','items'=>[['route','label','icon']]]] --}}
<div class="gg-sidebar" id="ggSidebar">
    <div class="brand">
        <i class="bi bi-fuel-pump-fill"></i>
        <span class="brand-text">GeoGas<span style="color:var(--gg-accent);">ManFort</span></span>
        <button type="button" class="gg-sidebar-mini-toggle" id="ggMiniToggle" title="Collapse sidebar" aria-label="Collapse or expand sidebar"><i class="bi bi-layout-sidebar-inset"></i></button>
    </div>
    <button class="btn btn-sm w-100 d-md-none mb-2" type="button" style="background:transparent;border:1px solid #444;color:#fff;" onclick="document.getElementById('sidebarLinks').classList.toggle('show')" aria-controls="sidebarLinks">
        <i class="bi bi-list me-1"></i> Menu
    </button>
    <div class="sidebar-links d-md-flex flex-column" id="sidebarLinks">
        <div>
        @foreach($items as $gi => $group)
            @php
                $hasActive = collect($group['items'])->contains(fn ($i) => $i['route'] === $active);
                $gid = 'ggGroup'.$gi;
            @endphp
            <button type="button" class="gg-sidebar-group-toggle {{ $hasActive ? 'has-active' : '' }}"
                    data-bs-toggle="collapse" data-bs-target="#{{ $gid }}"
                    aria-expanded="{{ $hasActive ? 'true' : 'false' }}" aria-controls="{{ $gid }}" title="{{ $group['label'] }}">
                <i class="bi {{ $group['icon'] }}"></i>
                <span class="gg-link-text">{{ $group['label'] }}</span>
                <i class="bi bi-chevron-down gg-chevron"></i>
            </button>
            <div class="collapse gg-sidebar-sub {{ $hasActive ? 'show' : '' }}" id="{{ $gid }}">
                @foreach($group['items'] as $item)
                    <a href="{{ route($item['route']) }}" class="gg-sidebar-link {{ $active === $item['route'] ? 'active' : '' }}"
                       title="{{ $item['label'] }}" @if($active === $item['route']) aria-current="page" @endif>
                        <i class="bi {{ $item['icon'] }}"></i> <span class="gg-link-text">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
        </div>

        <form method="POST" action="{{ route('logout') }}" class="gg-sidebar-logout">
            @csrf
            <button type="submit" class="gg-sidebar-link" title="Log out"><i class="bi bi-box-arrow-right"></i> <span class="gg-link-text">Log Out</span></button>
        </form>
    </div>
</div>
<script>
(function () {
    var KEY = 'ggSidebarMini';
    function apply(on) { document.body.classList.toggle('gg-sidebar-mini', on); }
    try { apply(localStorage.getItem(KEY) === '1'); } catch (e) {}
    document.getElementById('ggMiniToggle').addEventListener('click', function () {
        var on = !document.body.classList.contains('gg-sidebar-mini');
        apply(on);
        try { localStorage.setItem(KEY, on ? '1' : '0'); } catch (e) {}
    });
})();
</script>
