@props(['news'])
<div class="gg-news-card">
    <div class="image">
        @if($news->image_path)
            <img src="{{ $news->imageUrl() }}" alt="{{ $news->title }}">
        @else
            <div class="gg-news-card-placeholder"><i class="bi bi-megaphone-fill"></i></div>
        @endif
    </div>
    <div class="body">
        <span class="badge mb-1" style="background:var(--gg-accent-light); color:var(--gg-dark); font-weight:600; font-size:.68rem;">{{ \App\Models\News::CATEGORIES[$news->category] }}</span>
        <h6 class="mb-1">{{ $news->title }}</h6>
        <p class="text-muted-gg small mb-2">{{ \Illuminate\Support\Str::limit(strip_tags($news->content), 90) }}</p>
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted-gg" style="font-size:.72rem;">{{ optional($news->published_at)->format('M d, Y') }}</span>
            <a href="{{ route('news.show', $news) }}" class="small fw-bold" style="color:var(--gg-accent);">Read More →</a>
        </div>
    </div>
</div>
