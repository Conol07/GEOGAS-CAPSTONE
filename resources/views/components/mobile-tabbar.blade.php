<nav class="gg-tabbar d-md-none">
    <a href="{{ route('home') }}" class="gg-tab {{ request()->routeIs('home') ? 'active' : '' }}">
        <i class="bi bi-house-fill"></i><span>Home</span>
    </a>
    <a href="{{ route('stations.index') }}" class="gg-tab {{ request()->routeIs('stations.*') ? 'active' : '' }}">
        <i class="bi bi-shop"></i><span>Station</span>
    </a>
    <a href="{{ route('cheapest') }}" class="gg-tab {{ request()->routeIs('cheapest') ? 'active' : '' }}">
        <i class="bi bi-trophy-fill"></i><span>Cheapest</span>
    </a>
    <a href="{{ route('complaints.create') }}" class="gg-tab {{ request()->routeIs('complaints.*') ? 'active' : '' }}">
        <i class="bi bi-flag-fill"></i><span>Report</span>
    </a>
    <button type="button" id="tabbarSearchBtn" class="gg-tab border-0 bg-transparent">
        <i class="bi bi-search"></i><span>Search</span>
    </button>
</nav>

@push('scripts')
<script>
    document.getElementById('tabbarSearchBtn').addEventListener('click', function () {
        const navMain = document.getElementById('navMain');
        const toggler = document.querySelector('.navbar-toggler');
        if (navMain && !navMain.classList.contains('show') && toggler) {
            toggler.click();
        }
        setTimeout(() => {
            const input = document.getElementById('navSearchInput');
            if (input) { input.scrollIntoView({ behavior: 'smooth', block: 'center' }); input.focus(); }
        }, 250);
    });
</script>
@endpush
