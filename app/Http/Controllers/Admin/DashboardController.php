<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FuelPrice;
use App\Models\GasolineStation;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_stations' => GasolineStation::count(),
            'active_stations' => GasolineStation::active()->count(),
            'pending_updates' => FuelPrice::pending()->count(),
            'approved_updates' => FuelPrice::approved()->count(),
            'rejected_updates' => FuelPrice::rejected()->count(),
            'total_personnel' => User::where('role', 'station_personnel')->count(),
        ];

        $recentUpdates = FuelPrice::with(['station', 'submitter'])
            ->latest('created_at')
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentUpdates'));
    }
}
