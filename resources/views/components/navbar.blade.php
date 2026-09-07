<nav class="navbar navbar-expand-lg gg-navbar sticky-top py-2">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="bi bi-fuel-pump-fill"></i>
            <span>GeoGas</span><span style="color:var(--gg-accent);">ManFort</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto ms-lg-3">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('stations.*') ? 'active' : '' }}" href="{{ route('stations.index') }}">Stations</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('compare') ? 'active' : '' }}" href="{{ route('compare') }}">Compare</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('areas') ? 'active' : '' }}" href="{{ route('areas') }}">By Area</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('cheapest') ? 'active' : '' }}" href="{{ route('cheapest') }}">Cheapest Fuel</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('map') ? 'active' : '' }}" href="{{ route('map') }}">Map</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('complaints.*') ? 'active' : '' }}" href="{{ route('complaints.create') }}"><i class="bi bi-flag-fill me-1"></i>Report an Issue</a></li>
            </ul>
            <ul class="navbar-nav align-items-lg-center gap-lg-2">
                @auth
                    @if(auth()->user()->isLguAdmin())
                        <li class="nav-item"><a class="btn btn-outline-brand btn-sm" href="{{ route('lgu.dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>LGU Dashboard</a></li>
                    @else
                        <li class="nav-item"><a class="btn btn-outline-brand btn-sm" href="{{ route('station.dashboard') }}"><i class="bi bi-shop me-1"></i>My Station</a></li>
                    @endif
                    <li class="nav-item mt-2 mt-lg-0">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-link text-muted-gg px-2" type="submit">Logout</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item mt-2 mt-lg-0"><a class="btn btn-brand btn-sm" href="{{ route('login') }}">Station / LGU Login</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
