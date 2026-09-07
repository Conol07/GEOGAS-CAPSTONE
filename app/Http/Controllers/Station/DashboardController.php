<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
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
        $currentPrices = $station->currentPrices();

        $recentUpdates = $station->fuelPrices()->with('fuelType', 'updater')->latest('created_at')->take(6)->get();
        $recentComplaints = Complaint::where('station_id', $station->id)->latest('created_at')->take(5)->get();
        $staffCount = $station->personnel()->whereHas('user', fn ($q) => $q->where('role', 'staff'))->count();

        return view('station.dashboard', compact('station', 'currentPrices', 'recentUpdates', 'recentComplaints', 'staffCount'));
    }
}
