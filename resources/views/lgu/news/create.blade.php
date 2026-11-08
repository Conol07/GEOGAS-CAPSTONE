@php
$sidebarItems = \App\Support\Nav::lguSidebar();
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.news.index'])
@section('title', 'Add News')

@section('dashboard-content')
<h4 class="mb-1">Add News / Announcement</h4>
<p class="text-muted-gg small mb-3">If related to a station, its details are pulled in automatically wherever this article links out to it.</p>
<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('lgu.news.store') }}" enctype="multipart/form-data">
            @csrf
            @include('lgu.news._form')
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Save</button>
                <a href="{{ route('lgu.news.index') }}" class="btn btn-outline-brand">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
