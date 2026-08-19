<?php

namespace App\Http\Controllers;

use App\Models\FuelPrice;
use App\Models\GasolineStation;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        $stations = GasolineStation::active()
            ->with('latestApprovedPrice')
            ->orderBy('station_name')
            ->take(6)
            ->get();

        $stats = [
            'total_stations' => GasolineStation::active()->count(),
            'updates_today' => FuelPrice::approved()->whereDate('verified_at', today())->count(),
        ];

        return view('public.home', compact('stations', 'stats'));
    }

    public function stations(Request $request)
    {
        $stations = GasolineStation::active()
            ->search($request->input('q'))
            ->with('latestApprovedPrice')
            ->orderBy('station_name')
            ->paginate(9)
            ->withQueryString();

        return view('public.stations.index', compact('stations'));
    }

    public function show(GasolineStation $station)
    {
        $station->load(['latestApprovedPrice', 'fuelPrices' => function ($q) {
            $q->approved()->latest('effective_date')->take(10);
        }]);

        return view('public.stations.show', compact('station'));
    }

    public function compare(Request $request)
    {
        $sort = $request->input('sort', 'name');

        $query = GasolineStation::active()->with('latestApprovedPrice');

        $stations = $query->get()->sortBy(function ($station) use ($sort) {
            return match ($sort) {
                'lowest' => $station->latestApprovedPrice->gasoline_price ?? PHP_INT_MAX,
                'highest' => -($station->latestApprovedPrice->gasoline_price ?? -1),
                'updated' => optional($station->latestApprovedPrice)->effective_date,
                default => $station->station_name,
            };
        })->values();

        return view('public.compare', compact('stations', 'sort'));
    }

    public function map()
    {
        $stations = GasolineStation::active()
            ->with('latestApprovedPrice')
            ->get(['id', 'station_name', 'address', 'latitude', 'longitude']);

        return view('public.map', compact('stations'));
    }
}
