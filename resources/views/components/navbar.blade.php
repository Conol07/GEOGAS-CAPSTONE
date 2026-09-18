<nav class="navbar navbar-expand-md gg-navbar sticky-top py-2">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="bi bi-fuel-pump-fill"></i>
            <span>GeoGas</span><span style="color:var(--gg-accent);">ManFort</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto ms-md-3">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}"><i class="bi bi-house-fill me-1 d-md-none"></i>Home</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('stations.*') ? 'active' : '' }}" href="{{ route('stations.index') }}"><i class="bi bi-shop me-1 d-md-none"></i>Station</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('cheapest') ? 'active' : '' }}" href="{{ route('cheapest') }}"><i class="bi bi-trophy-fill me-1 d-md-none"></i>Cheapest Fuel</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('complaints.*') ? 'active' : '' }}" href="{{ route('complaints.create') }}"><i class="bi bi-flag-fill me-1 d-md-none"></i>Report an Issue</a></li>
            </ul>

            {{-- Search bar: station name, barangay/area, or fuel type. No login required. --}}
            <div class="gg-nav-search position-relative my-2 my-md-0 me-md-3" id="navSearchWrap">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted-gg"></i></span>
                    <input type="text" id="navSearchInput" class="form-control border-start-0" placeholder="Search station, area, or fuel..." autocomplete="off">
                </div>
                <div id="navSearchResults" class="gg-search-dropdown d-none"></div>
            </div>

            @auth
            <ul class="navbar-nav align-items-md-center gap-md-2">
                @if(auth()->user()->isLguAdmin())
                    <li class="nav-item"><a class="btn btn-outline-brand btn-sm" href="{{ route('lgu.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>LGU Dashboard</a></li>
                @else
                    <li class="nav-item"><a class="btn btn-outline-brand btn-sm" href="{{ route('station.dashboard') }}"><i class="bi bi-person-workspace me-1"></i>My Station</a></li>
                @endif
                <li class="nav-item mt-2 mt-md-0">
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-link text-muted-gg px-2" type="submit">Logout</button>
                    </form>
                </li>
            </ul>
            @endauth
        </div>
    </div>
</nav>

@push('scripts')
<script>
(function () {
    const input = document.getElementById('navSearchInput');
    const results = document.getElementById('navSearchResults');
    let debounceTimer = null;

    const availabilityDot = {
        enough: '#15803D', almost_empty: '#B45309', no_fuel: '#B91C1C', unknown: '#6B7280',
    };

    function logoOrPlaceholder(url, size) {
        return url
            ? `<img src="${url}" style="width:${size}px;height:${size}px;border-radius:8px;object-fit:cover;border:1px solid #E5E5E5;flex-shrink:0;">`
            : `<div style="width:${size}px;height:${size}px;border-radius:8px;background:#FFF1E6;color:#F58220;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="bi bi-fuel-pump-fill"></i></div>`;
    }

    function renderResults(items) {
        if (!items.length) {
            results.innerHTML = '<div class="gg-search-empty">No matching stations found.</div>';
            results.classList.remove('d-none');
            return;
        }
        results.innerHTML = items.map(s => {
            const dot = availabilityDot[s.availability] || availabilityDot.unknown;
            const priceLine = s.highlighted_price
                ? `<div class="small" style="color:${availabilityDot[s.highlighted_price.availability_status] || '#6B7280'};">${s.highlighted_price.fuel_type}: ₱${s.highlighted_price.price.toFixed(2)}</div>`
                : '';
            return `<a href="/stations/${s.id}" class="gg-search-result">
                        ${logoOrPlaceholder(s.logo_url, 36)}
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">${s.station_name}</div>
                            <div class="text-muted-gg" style="font-size:.75rem;">${s.address}, ${s.barangay}</div>
                            ${priceLine}
                        </div>
                        <span style="color:${dot};">●</span>
                    </a>`;
        }).join('');
        results.classList.remove('d-none');
    }

    input.addEventListener('input', function () {
        const q = this.value.trim();
        clearTimeout(debounceTimer);
        if (q.length < 2) { results.classList.add('d-none'); return; }
        debounceTimer = setTimeout(() => {
            fetch(`{{ route('search') }}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(renderResults)
                .catch(() => results.classList.add('d-none'));
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (!document.getElementById('navSearchWrap').contains(e.target)) {
            results.classList.add('d-none');
        }
    });
})();
</script>
@endpush
