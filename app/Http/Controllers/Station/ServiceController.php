<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\StationService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function edit(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $station = $assignment->station;
        $services = $station->services()->get()->keyBy('service_key');

        return view('station.services', compact('station', 'services'));
    }

    public function update(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        foreach (StationService::CATALOG as $key => $label) {
            StationService::updateOrCreate(
                ['station_id' => $assignment->station_id, 'service_key' => $key],
                ['label' => $label, 'available' => $request->boolean("service_{$key}")]
            );
        }

        AuditLog::record($request->user(), 'Updated station services', null, "Station #{$assignment->station_id}");

        return back()->with('status', 'Station services updated.');
    }
}
