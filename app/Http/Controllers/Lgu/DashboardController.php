<?php

namespace App\Http\Controllers\Lgu;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\FuelPrice;
use App\Models\GasolineStation;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stations = GasolineStation::all();

        $availabilityCounts = ['enough' => 0, 'almost_empty' => 0, 'no_fuel' => 0, 'unknown' => 0];
        foreach ($stations as $station) {
            $availabilityCounts[$station->overallAvailability()]++;
        }

        $latestPriceIds = FuelPrice::selectRaw('MAX(id) as id')->groupBy('station_id', 'fuel_type_id')->pluck('id');
        $averagePrice = FuelPrice::whereIn('id', $latestPriceIds)->avg('price');

        $stats = [
            'total_stations' => $stations->count(),
            'active_stations' => $stations->where('status', 'active')->count(),
            'inactive_stations' => $stations->where('status', 'inactive')->count(),
            'enough_fuel' => $availabilityCounts['enough'],
            'almost_empty' => $availabilityCounts['almost_empty'],
            'no_fuel' => $availabilityCounts['no_fuel'],
            'average_price' => $averagePrice,
            'total_complaints' => Complaint::count(),
            'pending_complaints' => Complaint::where('status', 'pending')->count(),
            'total_personnel' => User::whereIn('role', ['manager', 'staff'])->count(),
        ];

        $recentPriceUpdates = FuelPrice::with(['station', 'fuelType', 'updater'])
            ->latest('created_at')->take(8)->get();

        $recentComplaints = Complaint::with('station')->latest('created_at')->take(5)->get();

        return view('lgu.dashboard', compact('stats', 'recentPriceUpdates', 'recentComplaints'));
    }
}
