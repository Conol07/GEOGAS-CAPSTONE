@php
$sidebarItems = \App\Support\Nav::lguSidebar();
@endphp
@extends('layouts.dashboard', ['sidebarItems' => $sidebarItems, 'activeRoute' => 'lgu.news.index'])
@section('title', 'News & Announcements')

@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">News &amp; Announcements</h4>
        <p class="text-muted-gg small mb-0">Publish fuel-related updates for the public dashboard.</p>
    </div>
    <a href="{{ route('lgu.news.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i>Add News</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="draft" @selected(request('status')=='draft')>Draft</option>
            <option value="published" @selected(request('status')=='published')>Published</option>
            <option value="archived" @selected(request('status')=='archived')>Archived</option>
        </select>
    </div>
    <div class="col-md-3">
        <select name="category" class="form-select" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach(\App\Models\News::CATEGORIES as $key => $label)
                <option value="{{ $key }}" @selected(request('category')==$key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</form>

@if($news->isEmpty())
    <x-empty-state icon="bi-megaphone" title="No news yet" message="Create your first announcement." />
@else
<div class="gg-table gg-table-responsive">
    <table class="table">
        <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Published</th><th>Author</th><th class="no-print">Actions</th></tr></thead>
        <tbody>
        @foreach($news as $n)
            <tr>
                <td class="fw-semibold small">{{ $n->title }}</td>
                <td class="small">{{ \App\Models\News::CATEGORIES[$n->category] }}</td>
                <td><span class="status-badge {{ $n->status=='published' ? 'status-approved' : ($n->status=='archived' ? 'status-inactive' : 'status-pending') }}">{{ ucfirst($n->status) }}</span></td>
                <td class="text-muted-gg small">{{ optional($n->published_at)->format('M d, Y') ?? '—' }}</td>
                <td class="small">{{ $n->author->name }}</td>
                <td>
                    <div class="d-flex gap-1 flex-wrap">
                        <a href="{{ route('lgu.news.edit', $n) }}" class="btn btn-sm btn-outline-brand">Edit</a>
                        @if($n->status !== 'archived')
                        <form method="POST" action="{{ route('lgu.news.archive', $n) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-outline-brand">Archive</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('lgu.news.destroy', $n) }}" onsubmit="return confirm('Delete this news item permanently?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $news->links() }}</div>
@endif
@endsection
