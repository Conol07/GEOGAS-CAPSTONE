<?php

namespace App\Http\Controllers\Lgu;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FuelType;
use App\Models\GasolineStation;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::with(['station', 'author'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->filled('scope'), fn ($q) => $q->where('scope', $request->input('scope')))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('lgu.news.index', compact('news'));
    }

    public function create()
    {
        $stations = GasolineStation::orderBy('station_name')->get(['id', 'station_name']);
        $fuelTypes = FuelType::ordered()->get();

        return view('lgu.news.create', compact('stations', 'fuelTypes'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateNews($request);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('news', 'public');
        }

        $validated['created_by'] = $request->user()->id;

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $news = News::create($validated);

        AuditLog::record($request->user(), 'Created news/announcement', $news, $news->title);

        return redirect()->route('lgu.news.index')->with('status', 'News item saved.');
    }

    public function edit(News $news)
    {
        $stations = GasolineStation::orderBy('station_name')->get(['id', 'station_name']);
        $fuelTypes = FuelType::ordered()->get();

        return view('lgu.news.edit', compact('news', 'stations', 'fuelTypes'));
    }

    public function update(Request $request, News $news)
    {
        $validated = $this->validateNews($request);

        if ($request->hasFile('image')) {
            if ($news->image_path) {
                Storage::disk('public')->delete($news->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('news', 'public');
        } elseif ($request->boolean('remove_image') && $news->image_path) {
            Storage::disk('public')->delete($news->image_path);
            $validated['image_path'] = null;
        }

        if ($validated['status'] === 'published' && ! $news->published_at && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $news->update($validated);

        AuditLog::record($request->user(), 'Updated news/announcement', $news, $news->title);

        return redirect()->route('lgu.news.index')->with('status', 'News item updated.');
    }

    /** "Fetch Philippine fuel news now" button: runs the importer once. */
    public function import(Request $request)
    {
        if (! config('geogas.news_feeds')) {
            return back()->with('error', 'No news feeds are configured. Add GEOGAS_NEWS_FEEDS to the .env file first.');
        }

        $before = News::where('scope', 'national')->count();
        Artisan::call('geogas:import-national-news');
        $added = News::where('scope', 'national')->count() - $before;

        AuditLog::record($request->user(), 'Fetched national fuel news', null, "{$added} new item(s)");

        return back()->with('status', $added > 0
            ? "{$added} new Philippine fuel news item(s) fetched".(config('geogas.news_auto_publish') ? ' and published.' : ' as drafts. Review and publish them below.')
            : 'No new matching fuel news found right now.');
    }

    public function archive(Request $request, News $news)
    {
        $news->update(['status' => 'archived']);
        AuditLog::record($request->user(), 'Archived news/announcement', $news, $news->title);

        return back()->with('status', 'News item archived.');
    }

    public function destroy(Request $request, News $news)
    {
        if ($news->image_path) {
            Storage::disk('public')->delete($news->image_path);
        }

        AuditLog::record($request->user(), 'Deleted news/announcement', null, $news->title);
        $news->delete();

        return redirect()->route('lgu.news.index')->with('status', 'News item deleted.');
    }

    private function validateNews(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:'.implode(',', array_keys(News::CATEGORIES))],
            'scope' => ['required', 'in:local,national'],
            'source_name' => ['required_if:scope,national', 'nullable', 'string', 'max:120'],
            'source_url' => ['required_if:scope,national', 'nullable', 'url:http,https', 'max:500'],
            'content' => ['required', 'string', 'max:5000'],
            'status' => ['required', 'in:draft,published,archived'],
            'published_at' => ['nullable', 'date'],
            'related_station_id' => ['nullable', 'exists:gasoline_stations,id'],
            'related_barangay' => ['nullable', 'string', 'max:100'],
            'related_fuel_type_id' => ['nullable', 'exists:fuel_types,id'],
            'previous_price' => ['nullable', 'numeric', 'min:0'],
            'current_price' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        unset($validated['image'], $validated['remove_image']);

        // Source details only make sense for national news.
        if ($validated['scope'] === 'local') {
            $validated['source_name'] = null;
            $validated['source_url'] = null;
        }

        return $validated;
    }
}
