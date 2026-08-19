<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FuelPrice;
use App\Models\GasolineStation;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function stations()
    {
        $stations = GasolineStation::with('latestApprovedPrice')->orderBy('station_name')->get();

        return view('admin.reports.stations', compact('stations'));
    }

    public function priceHistory(Request $request)
    {
        $status = $request->input('status', 'all');

        $history = FuelPrice::with(['station', 'submitter', 'verifier'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->whereBetween('created_at', [
                $request->input('from', now()->subMonth()),
                $request->input('to', now()),
            ])
            ->latest('created_at')
            ->get();

        return view('admin.reports.price-history', compact('history', 'status'));
    }
}
