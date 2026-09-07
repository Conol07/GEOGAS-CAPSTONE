<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\FuelPrice;
use App\Models\FuelType;
use App\Models\GasolineStation;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        $stations = GasolineStation::active()->orderBy('station_name')->take(6)->get();
        $fuelTypes = FuelType::ordered()->get();

        $latestIds = FuelPrice::selectRaw('MAX(id) as id')->groupBy('station_id', 'fuel_type_id')->pluck('id');
        $currentPrices = FuelPrice::whereIn('id', $latestIds)->get();

        $stats = [
            'total_stations' => GasolineStation::active()->count(),
            'updates_today' => FuelPrice::whereDate('created_at', today())->count(),
            'fuel_types' => $fuelTypes->count(),
        ];

        return view('public.home', compact('stations', 'stats', 'fuelTypes', 'currentPrices'));
    }

    public function stations(Request $request)
    {
        $stations = GasolineStation::active()
            ->search($request->input('q'))
            ->inBarangay($request->input('barangay'))
            ->orderBy('station_name')
            ->paginate(9)
            ->withQueryString();

        // Attach current prices + availability to each without N+1
        $stations->getCollection()->transform(function ($station) {
            $station->setRelation('current', $station->currentPrices());
            return $station;
        });

        if ($request->filled('availability')) {
            $stations->setCollection(
                $stations->getCollection()->filter(fn ($s) => $s->overallAvailability() === $request->input('availability'))->values()
            );
        }

        $barangays = GasolineStation::select('barangay')->distinct()->orderBy('barangay')->pluck('barangay');

        return view('public.stations.index', compact('stations', 'barangays'));
    }

    public function show(GasolineStation $station)
    {
        $currentPrices = $station->currentPrices();
        $services = $station->services()->where('available', true)->get();

        $history = $station->fuelPrices()->with('fuelType')->latest('created_at')->take(15)->get();

        return view('public.stations.show', compact('station', 'currentPrices', 'services', 'history'));
    }

    public function compare(Request $request)
    {
        $fuelTypes = FuelType::ordered()->get();
        $fuelTypeId = $request->input('fuel_type_id', optional($fuelTypes->first())->id);
        $sort = $request->input('sort', 'lowest');

        $latestIds = FuelPrice::where('fuel_type_id', $fuelTypeId)
            ->selectRaw('MAX(id) as id')->groupBy('station_id')->pluck('id');

        $prices = FuelPrice::with('station', 'fuelType')
            ->whereIn('id', $latestIds)
            ->whereHas('station', fn ($q) => $q->active())
            ->get();

        $prices = (match ($sort) {
            'lowest' => $prices->sortBy('price'),
            'highest' => $prices->sortByDesc('price'),
            'name' => $prices->sortBy('station.station_name'),
            'updated' => $prices->sortByDesc('created_at'),
            default => $prices,
        })->values();

        return view('public.compare', compact('fuelTypes', 'fuelTypeId', 'sort', 'prices'));
    }

    public function map(Request $request)
    {
        $stations = GasolineStation::active()->get(['id', 'station_name', 'address', 'barangay', 'latitude', 'longitude']);

        $stations = $stations->map(function ($station) {
            $station->current_prices_list = $station->currentPrices()->values();
            $station->availability = $station->overallAvailability();
            return $station;
        });

        if ($request->filled('availability')) {
            $stations = $stations->where('availability', $request->input('availability'))->values();
        }

        return view('public.map', compact('stations'));
    }

    /**
     * Area / Barangay price view — number of stations, avg/low/high price
     * and price range per barangay, for the selected (or first) fuel type.
     */
    public function areas(Request $request)
    {
        $fuelTypes = FuelType::ordered()->get();
        $fuelTypeId = $request->input('fuel_type_id', optional($fuelTypes->first())->id);

        $latestIds = FuelPrice::where('fuel_type_id', $fuelTypeId)
            ->selectRaw('MAX(id) as id')->groupBy('station_id')->pluck('id');

        $prices = FuelPrice::with('station')->whereIn('id', $latestIds)
            ->whereHas('station', fn ($q) => $q->active())
            ->get();

        $areas = $prices->groupBy('station.barangay')->map(function ($rows, $barangay) {
            return [
                'barangay' => $barangay,
                'station_count' => $rows->pluck('station.id')->unique()->count(),
                'average' => round($rows->avg('price'), 2),
                'lowest' => round($rows->min('price'), 2),
                'highest' => round($rows->max('price'), 2),
            ];
        })->values()->sortBy('barangay');

        return view('public.areas', compact('fuelTypes', 'fuelTypeId', 'areas'));
    }

    /**
     * Cheapest-fuel finder for a selected fuel type, available stations only.
     */
    public function cheapest(Request $request)
    {
        $fuelTypes = FuelType::ordered()->get();
        $fuelTypeId = $request->input('fuel_type_id', optional($fuelTypes->first())->id);

        $latestIds = FuelPrice::where('fuel_type_id', $fuelTypeId)
            ->selectRaw('MAX(id) as id')->groupBy('station_id')->pluck('id');

        $prices = FuelPrice::with('station')
            ->whereIn('id', $latestIds)
            ->where('availability_status', '!=', 'no_fuel')
            ->whereHas('station', fn ($q) => $q->active())
            ->get()
            ->sortBy('price')
            ->values();

        return view('public.cheapest', compact('fuelTypes', 'fuelTypeId', 'prices'));
    }

    /**
     * Nearest-station finder: browser supplies the user's coordinates via
     * geolocation JS, this endpoint returns stations sorted by distance
     * (haversine, computed in PHP — station counts are small enough that
     * this avoids requiring a spatial DB extension).
     */
    public function nearest(Request $request)
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $stations = GasolineStation::active()->get();

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
            'distance_km' => round($s->distance_km, 2),
            'availability' => $s->availability,
            'prices' => $s->current_prices_list->map(fn ($p) => [
                'fuel_type' => $p->fuelType->name,
                'price' => (float) $p->price,
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
