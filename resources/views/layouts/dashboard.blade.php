@extends('layouts.app')

@section('content')
<div class="gg-admin-shell">
    <x-sidebar :items="$sidebarItems" :active="$activeRoute" />
    <div class="gg-main">
        @yield('dashboard-content')
    </div>
</div>
@endsection
