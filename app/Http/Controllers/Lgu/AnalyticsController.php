<?php

namespace App\Http\Controllers\Lgu;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\FuelPrice;
use App\Models\FuelType;
use App\Models\GasolineStation;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $fuelTypes = FuelType::ordered()->get();
        $barangays = GasolineStation::select('barangay')->distinct()->orderBy('barangay')->pluck('barangay');

        $latestIdsQuery = FuelPrice::query()
            ->when($request->filled('from'), fn ($q) => $q->where('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->where('created_at', '<=', $request->input('to')));
        $latestIds = $latestIdsQuery->selectRaw('MAX(id) as id')->groupBy('station_id', 'fuel_type_id')->pluck('id');

        $currentPrices = FuelPrice::with(['station', 'fuelType'])
            ->whereIn('id', $latestIds)
            ->when($request->filled('fuel_type_id'), fn ($q) => $q->where('fuel_type_id', $request->input('fuel_type_id')))
            ->when($request->filled('barangay'), fn ($q) => $q->whereHas('station', fn ($s) => $s->where('barangay', $request->input('barangay'))))
            ->get();

        // Price analytics per fuel type
        $priceByFuelType = $currentPrices->groupBy('fuelType.name')->map(function ($rows) {
            return [
                'average' => round($rows->avg('price'), 2),
                'lowest' => round($rows->min('price'), 2),
                'highest' => round($rows->max('price'), 2),
            ];
        });

        // Price comparison per barangay (uses the currently selected fuel type, or all)
        $priceByBarangay = $currentPrices->groupBy('station.barangay')->map(function ($rows) {
            return [
                'average' => round($rows->avg('price'), 2),
                'lowest' => round($rows->min('price'), 2),
                'highest' => round($rows->max('price'), 2),
                'stations' => $rows->pluck('station.id')->unique()->count(),
            ];
        });

        // Station analytics
        $stations = GasolineStation::all();
        $stationsByBarangay = $stations->groupBy('barangay')->map->count();
        $availabilityCounts = ['enough' => 0, 'almost_empty' => 0, 'no_fuel' => 0, 'unknown' => 0];
        foreach ($stations as $s) {
            $availabilityCounts[$s->overallAvailability()]++;
        }

        // Complaint analytics
        $complaints = Complaint::query()
            ->when($request->filled('complaint_status'), fn ($q) => $q->where('status', $request->input('complaint_status')))
            ->get();
        $complaintsByCategory = $complaints->groupBy('category')->map->count();
        $complaintsByStation = $complaints->whereNotNull('station_id')
            ->groupBy('station_id')
            ->map->count();

        // Price trend (last 30 days, daily average across all stations for chart)
        $trend = FuelPrice::selectRaw('DATE(created_at) as day, AVG(price) as avg_price')
            ->where('created_at', '>=', now()->subDays(30))
            ->when($request->filled('fuel_type_id'), fn ($q) => $q->where('fuel_type_id', $request->input('fuel_type_id')))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return view('lgu.analytics.index', compact(
            'fuelTypes', 'barangays', 'priceByFuelType', 'priceByBarangay',
            'stationsByBarangay', 'availabilityCounts', 'complaintsByCategory',
            'complaintsByStation', 'trend'
        ));
    }
}
