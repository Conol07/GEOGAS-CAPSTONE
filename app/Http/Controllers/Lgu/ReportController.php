<?php

namespace App\Http\Controllers\Lgu;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\FuelPrice;
use App\Models\GasolineStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;

class ReportController extends Controller
{
    public function index()
    {
        return view('lgu.reports.index');
    }

    public function fuelPrices(Request $request)
    {
        $latestIds = FuelPrice::selectRaw('MAX(id) as id')->groupBy('station_id', 'fuel_type_id')->pluck('id');
        $prices = FuelPrice::with(['station', 'fuelType'])
            ->whereIn('id', $latestIds)
            ->when($request->filled('barangay'), fn ($q) => $q->whereHas('station', fn ($s) => $s->where('barangay', $request->input('barangay'))))
            ->orderBy('station_id')
            ->get();

        if ($request->input('export') === 'csv') {
            return $this->csv('fuel-price-report', ['Station', 'Barangay', 'Fuel Type', 'Price', 'Availability', 'Updated'], $prices->map(fn ($p) => [
                $p->station->station_name, $p->station->barangay, $p->fuelType->name,
                number_format($p->price, 2), $p->availability_status, $p->created_at->format('Y-m-d H:i'),
            ]));
        }

        $barangays = GasolineStation::select('barangay')->distinct()->orderBy('barangay')->pluck('barangay');

        return view('lgu.reports.fuel-prices', compact('prices', 'barangays'));
    }

    public function stations(Request $request)
    {
        $stations = GasolineStation::with('services')->orderBy('station_name')->get();

        if ($request->input('export') === 'csv') {
            return $this->csv('station-report', ['Station', 'Address', 'Barangay', 'Status', 'Availability'], $stations->map(fn ($s) => [
                $s->station_name, $s->address, $s->barangay, $s->status, $s->overallAvailability(),
            ]));
        }

        return view('lgu.reports.stations', compact('stations'));
    }

    public function complaints(Request $request)
    {
        $complaints = Complaint::with('station')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('station_id'), fn ($q) => $q->where('station_id', $request->input('station_id')))
            ->latest('created_at')
            ->get();

        if ($request->input('export') === 'csv') {
            return $this->csv('complaint-report', ['Reference', 'Category', 'Station', 'Station Address', 'Area', 'Submitted', 'Status', 'Action Taken', 'Resolved'], $complaints->map(fn ($c) => [
                $c->reference_no, \App\Models\Complaint::CATEGORIES[$c->category] ?? $c->category,
                optional($c->station)->station_name ?? 'N/A', optional($c->station)->address ?? 'N/A',
                optional($c->station)->barangay ?? 'N/A', $c->created_at->format('Y-m-d'), $c->status,
                $c->lgu_response ?? '', optional($c->resolved_at)?->format('Y-m-d') ?? '',
            ]));
        }

        $stations = GasolineStation::orderBy('station_name')->get(['id', 'station_name']);
        $selectedStation = $request->filled('station_id')
            ? GasolineStation::find($request->input('station_id'))
            : null;

        return view('lgu.reports.complaints', compact('complaints', 'stations', 'selectedStation'));
    }

    /**
     * Full record for a single station: info, current + historical prices,
     * availability, services, and complaint history — for LGU record-keeping.
     */
    public function stationRecord(Request $request)
    {
        $stations = GasolineStation::orderBy('station_name')->get(['id', 'station_name']);

        $station = $request->filled('station_id')
            ? GasolineStation::with('services')->findOrFail($request->input('station_id'))
            : null;

        $priceHistory = null;
        $complaints = null;
        $currentPrices = null;

        if ($station) {
            $currentPrices = $station->currentPrices();
            $priceHistory = $station->fuelPrices()->with('fuelType', 'updater')->latest('created_at')->take(50)->get();
            $complaints = $station->complaints()->latest('created_at')->get();
        }

        return view('lgu.reports.station-record', compact('stations', 'station', 'currentPrices', 'priceHistory', 'complaints'));
    }

    private function csv(string $filename, array $headers, $rows)
    {
        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return ResponseFacade::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'-'.now()->format('Ymd').'.csv"',
        ]);
    }
}
