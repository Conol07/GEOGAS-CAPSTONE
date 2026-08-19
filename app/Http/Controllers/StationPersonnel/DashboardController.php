<?php

namespace App\Http\Controllers\StationPersonnel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $assignment = $request->user()->stationAssignment;

        if (! $assignment) {
            return view('station.no-station');
        }

        $station = $assignment->station;
        $station->load(['latestApprovedPrice', 'pendingPrices']);

        $recentUpdates = $station->fuelPrices()
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('station.dashboard', compact('station', 'recentUpdates'));
    }
}
