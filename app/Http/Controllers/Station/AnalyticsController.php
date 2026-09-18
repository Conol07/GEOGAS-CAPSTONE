<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use App\Models\FuelType;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $station = $assignment->station;

        $history = $station->fuelPrices()->with('fuelType')->orderBy('created_at')->get();
        $historyByFuelType = $history->groupBy('fuel_type_id');

        // Only fuel types this station actually offers.
        $fuelTypes = FuelType::ordered()->get()->filter(fn ($ft) => $historyByFuelType->has($ft->id))->values();

        $currentPrices = $station->currentPrices();

        return view('station.analytics', compact('station', 'fuelTypes', 'historyByFuelType', 'currentPrices'));
    }
}
