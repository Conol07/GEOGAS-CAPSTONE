@php
$sidebarItems = \App\Support\Nav::lguSidebar();
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.news.index'])
@section('title', 'Edit News')

@section('dashboard-content')
<h4 class="mb-1">Edit News / Announcement</h4>
<div class="card">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('lgu.news.update', $news) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('lgu.news._form')
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-brand"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                <a href="{{ route('lgu.news.index') }}" class="btn btn-outline-brand">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
