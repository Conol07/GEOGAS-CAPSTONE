<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\FuelPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;

/**
 * Reports scoped strictly to the current manager/staff member's own station.
 */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $station = $assignment->station;

        $priceHistory = FuelPrice::with('fuelType', 'updater')
            ->where('station_id', $station->id)
            ->latest('created_at')
            ->take(100)
            ->get();

        $complaints = Complaint::where('station_id', $station->id)->latest('created_at')->get();

        if ($request->input('export') === 'csv') {
            abort_unless($request->user()->hasStationPermission('generate_reports'), 403, 'Your account does not have permission to export reports.');

            return $this->csv('station-price-history', ['Fuel Type', 'Price', 'Availability', 'Updated By', 'Date'], $priceHistory->map(fn ($p) => [
                $p->fuelType->name, number_format($p->price, 2), $p->availability_status, $p->updater->name, $p->created_at->format('Y-m-d H:i'),
            ]));
        }

        return view('station.reports.index', compact('station', 'priceHistory', 'complaints'));
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
