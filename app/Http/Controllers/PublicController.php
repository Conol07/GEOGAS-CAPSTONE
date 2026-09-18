<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\FuelPrice;
use App\Models\FuelType;
use App\Models\GasolineStation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicController extends Controller
{
    /**
     * Home = the main public dashboard. Centered on the interactive map;
     * everything else here is a lightweight supporting element, not a
     * full admin-style dashboard.
     */
    public function home()
    {
        $mapStations = $this->stationsForMap();

        $stats = ['total_stations' => GasolineStation::active()->count()];

        // Lowest & highest current price per fuel type, each pointing at the
        // specific station that has it — powers the clickable price-overview
        // cards on the dashboard.
        $priceOverview = $this->priceOverviewByFuelType();

        // A default (pre-location) preview of stations for the "Nearby" panel.
        $previewStations = $mapStations->sortBy('station_name')->take(5)->values();

        $updates = $this->recentActivityFeed();

        return view('public.home', [
            'stations' => $mapStations,
            'previewStations' => $previewStations,
            'stats' => $stats,
            'priceOverview' => $priceOverview,
            'updates' => $updates,
        ]);
    }

    /**
     * For every configured fuel type, find the station currently offering
     * the lowest price and the station currently offering the highest,
     * among active stations only. Used for the clickable price cards.
     */
    private function priceOverviewByFuelType()
    {
        $fuelTypes = FuelType::ordered()->get();

        return $fuelTypes->map(function ($ft) {
            $latestIds = FuelPrice::where('fuel_type_id', $ft->id)
                ->selectRaw('MAX(id) as id')->groupBy('station_id')->pluck('id');

            $prices = FuelPrice::with('station')
                ->whereIn('id', $latestIds)
                ->whereHas('station', fn ($q) => $q->active())
                ->get();

            if ($prices->isEmpty()) {
                return null;
            }

            return [
                'fuel_type' => $ft,
                'lowest' => $prices->sortBy('price')->first(),
                'highest' => $prices->sortByDesc('price')->first(),
            ];
        })->filter()->values();
    }

    /**
     * Real recent activity across all stations — price updates and
     * availability alerts — for the Home dashboard's "Latest Updates" panel.
     * Independent of geolocation so it's useful even without location access.
     */
    private function recentActivityFeed(int $limit = 6)
    {
        return FuelPrice::with(['station', 'fuelType'])
            ->whereHas('station', fn ($q) => $q->active())
            ->latest('created_at')
            ->take($limit)
            ->get()
            ->map(function ($p) {
                $type = match ($p->availability_status) {
                    'no_fuel' => 'no_fuel',
                    'almost_empty' => 'almost_empty',
                    default => 'price',
                };

                return [
                    'type' => $type,
                    'station' => $p->station->station_name,
                    'fuel_type' => $p->fuelType->name,
                    'price' => (float) $p->price,
                    'when' => $p->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * The Station page: search, barangay filter (folds in what used to be
     * the standalone "By Area" page — area stats appear when a barangay is
     * selected), availability filter, and a fuel-type-aware sort (folds in
     * what used to be the standalone "Compare" page).
     */
    public function stations(Request $request)
    {
        $fuelTypes = FuelType::ordered()->get();
        $fuelTypeId = $request->input('fuel_type_id', optional($fuelTypes->first())->id);
        $sort = $request->input('sort', 'name');
        $barangay = $request->input('barangay');

        $stations = GasolineStation::active()
            ->search($request->input('q'))
            ->inBarangay($barangay)
            ->get();

        $stations = $stations->map(function ($station) {
            $station->setRelation('current', $station->currentPrices());
            return $station;
        });

        if ($request->filled('availability')) {
            $stations = $stations->filter(fn ($s) => $s->overallAvailability() === $request->input('availability'))->values();
        }

        $stations = (match ($sort) {
            'lowest' => $stations->sortBy(fn ($s) => optional($s->current->get($fuelTypeId))->price ?? INF),
            'highest' => $stations->sortByDesc(fn ($s) => optional($s->current->get($fuelTypeId))->price ?? -INF),
            default => $stations->sortBy('station_name'),
        })->values();

        // Manual pagination since sorting happens on a computed (non-DB) value.
        $page = $request->input('page', 1);
        $perPage = 9;
        $paginated = new LengthAwarePaginator(
            $stations->forPage($page, $perPage),
            $stations->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $barangays = GasolineStation::select('barangay')->distinct()->orderBy('barangay')->pluck('barangay');

        // Area stats for the selected barangay only (folds in the old "By Area" page).
        $areaStats = null;
        if ($barangay) {
            $areaLatestIds = FuelPrice::where('fuel_type_id', $fuelTypeId)
                ->selectRaw('MAX(id) as id')->groupBy('station_id')->pluck('id');

            $areaPrices = FuelPrice::whereIn('id', $areaLatestIds)
                ->whereHas('station', fn ($q) => $q->active()->where('barangay', $barangay))
                ->get();

            if ($areaPrices->isNotEmpty()) {
                $areaStats = [
                    'barangay' => $barangay,
                    'station_count' => $areaPrices->pluck('station_id')->unique()->count(),
                    'lowest' => round($areaPrices->min('price'), 2),
                    'highest' => round($areaPrices->max('price'), 2),
                    'average' => round($areaPrices->avg('price'), 2),
                ];
            }
        }

        return view('public.stations.index', compact('paginated', 'barangays', 'fuelTypes', 'fuelTypeId', 'sort', 'areaStats'));
    }

    public function show(GasolineStation $station)
    {
        $currentPrices = $station->currentPrices();
        $services = $station->services()->where('available', true)->get();

        $history = $station->fuelPrices()->with('fuelType')->orderBy('created_at')->get();
        $historyByFuelType = $history->groupBy('fuel_type_id');
        $recentHistory = $history->sortByDesc('created_at')->take(15)->values();

        // Only fuel types this station actually offers — a station that never
        // configured Premium shouldn't show a placeholder Premium section.
        $fuelTypes = FuelType::ordered()->get()->filter(fn ($ft) => $historyByFuelType->has($ft->id))->values();

        $highlightFuelTypeId = request()->integer('highlight');

        return view('public.stations.show', compact('station', 'currentPrices', 'services', 'fuelTypes', 'historyByFuelType', 'recentHistory', 'highlightFuelTypeId'));
    }

    /**
     * Shared payload builder for Home's small dashboard map: every active
     * station with its current price/availability per fuel type and its
     * available services, ready to serialize to JSON for Leaflet.
     */
    private function stationsForMap()
    {
        $stations = GasolineStation::active()->with('services')->get();

        return $stations->map(function ($station) {
            $station->current_prices_list = $station->currentPrices()->values();
            $station->availability = $station->overallAvailability();
            $station->active_services = $station->services->where('available', true)->pluck('label')->values();
            $station->logo_url = $station->logoUrl();
            $station->photo_url = $station->photoUrl();
            return $station;
        })->values();
    }

    /**
     * Cheapest-fuel finder for a selected fuel type + optional barangay,
     * available stations only. Distance is shown when the browser supplies
     * the user's coordinates (optional — never required).
     */
    public function cheapest(Request $request)
    {
        $fuelTypes = FuelType::ordered()->get();
        $fuelTypeId = $request->input('fuel_type_id', optional($fuelTypes->first())->id);
        $barangay = $request->input('barangay');
        $barangays = GasolineStation::select('barangay')->distinct()->orderBy('barangay')->pluck('barangay');

        $latestIds = FuelPrice::where('fuel_type_id', $fuelTypeId)
            ->selectRaw('MAX(id) as id')->groupBy('station_id')->pluck('id');

        $prices = FuelPrice::with('station')
            ->whereIn('id', $latestIds)
            ->where('availability_status', '!=', 'no_fuel')
            ->whereHas('station', function ($q) use ($barangay) {
                $q->active();
                if ($barangay) {
                    $q->where('barangay', $barangay);
                }
            })
            ->get()
            ->sortBy('price')
            ->values();

        return view('public.cheapest', compact('fuelTypes', 'fuelTypeId', 'barangays', 'barangay', 'prices'));
    }

    /**
     * Nearest-station finder: browser supplies the user's coordinates via
     * geolocation JS, this endpoint returns stations sorted by distance
     * (haversine, computed in PHP — station counts are small enough that
     * this avoids requiring a spatial DB extension) along with enough
     * per-price detail to drive the Home page's location-based updates.
     */
    public function nearest(Request $request)
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $stations = GasolineStation::active()->with('services')->get();

        $ranked = $stations->map(function ($station) use ($validated) {
            $station->distance_km = $this->haversineKm(
                $validated['lat'], $validated['lng'], (float) $station->latitude, (float) $station->longitude
            );
            $station->current_prices_list = $station->currentPrices()->values();
            $station->availability = $station->overallAvailability();
            return $station;
        })->sortBy('distance_km')->take(10)->values();

        return response()->json($ranked->map(fn ($s) => [
            'id' => $s->id,
            'station_name' => $s->station_name,
            'address' => $s->address,
            'barangay' => $s->barangay,
            'distance_km' => round($s->distance_km, 2),
            'availability' => $s->availability,
            'logo_url' => $s->logoUrl(),
            'active_services' => $s->services->where('available', true)->pluck('label')->values(),
            'prices' => $s->current_prices_list->map(fn ($p) => [
                'fuel_type' => $p->fuelType->name,
                'price' => (float) $p->price,
                'availability_status' => $p->availability_status,
                'updated_at' => $p->created_at->toIso8601String(),
            ]),
        ]));
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    /**
     * Dynamic search for the nav search bar (JSON, no login required).
     * Matches station name, barangay/address/municipality, or a fuel type
     * name — searching "Diesel" surfaces stations that report Diesel, with
     * that fuel's current price/availability shown in the result.
     */
    public function search(Request $request)
    {
        $term = trim((string) $request->input('q', ''));

        if ($term === '' || mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $matchedFuelType = FuelType::where('name', 'like', "%{$term}%")->first();

        $stations = GasolineStation::active()
            ->where(function ($q) use ($term, $matchedFuelType) {
                $q->where('station_name', 'like', "%{$term}%")
                    ->orWhere('barangay', 'like', "%{$term}%")
                    ->orWhere('address', 'like', "%{$term}%")
                    ->orWhere('municipality', 'like', "%{$term}%");

                if ($matchedFuelType) {
                    $q->orWhereHas('fuelPrices', fn ($fp) => $fp->where('fuel_type_id', $matchedFuelType->id));
                }
            })
            ->limit(8)
            ->get();

        $results = $stations->map(function ($station) use ($matchedFuelType) {
            $current = $station->currentPrices();
            $highlighted = $matchedFuelType ? $current->get($matchedFuelType->id) : null;

            return [
                'id' => $station->id,
                'station_name' => $station->station_name,
                'address' => $station->address,
                'barangay' => $station->barangay,
                'availability' => $station->overallAvailability(),
                'logo_url' => $station->logoUrl(),
                'highlighted_price' => $highlighted ? [
                    'fuel_type' => $matchedFuelType->name,
                    'price' => (float) $highlighted->price,
                    'availability_status' => $highlighted->availability_status,
                ] : null,
            ];
        });

        return response()->json($results);
    }

    public function createComplaint()
    {
        $stations = GasolineStation::orderBy('station_name')->get(['id', 'station_name']);

        return view('public.complaint-form', compact('stations'));
    }

    public function storeComplaint(Request $request)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:100'],
            'category' => ['required', 'in:incorrect_price,incorrect_availability,incorrect_station_info,price_not_updated,fuel_unavailable_despite_shown,other'],
            'station_id' => ['nullable', 'exists:gasoline_stations,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:4096'],
            // honeypot: a hidden field real users never fill in
            'website' => ['size:0'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('complaints', 'public');
        }

        $complaint = Complaint::create([
            'reference_no' => Complaint::generateReferenceNo(),
            'name' => $validated['name'] ?? null,
            'contact' => $validated['contact'] ?? null,
            'category' => $validated['category'],
            'station_id' => $validated['station_id'] ?? null,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'photo_path' => $photoPath,
            'status' => 'pending',
            'submitter_ip' => $request->ip(),
        ]);

        return redirect()->route('complaints.confirmation', $complaint->reference_no);
    }

    public function complaintConfirmation(string $referenceNo)
    {
        $complaint = Complaint::where('reference_no', $referenceNo)->firstOrFail();

        return view('public.complaint-confirmation', compact('complaint'));
    }
}
