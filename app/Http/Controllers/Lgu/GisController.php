<?php

namespace App\Http\Controllers\Lgu;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\GasolineStation;
use App\Models\Setting;
use Illuminate\Http\Request;

class GisController extends Controller
{
    /** LGU GIS page: same data endpoint as the public map, bigger canvas, print-friendly. */
    public function index()
    {
        return view('lgu.gis.index', [
            'stationCount' => GasolineStation::active()->count(),
            'heatmapEnabled' => Setting::bool('heatmap_enabled', true),
            'heatmapAuto' => Setting::bool('heatmap_auto', true),
        ]);
    }

    /**
     * LGU switches for the PUBLIC map: whether the heatmap is offered at all,
     * and whether it is drawn automatically when the map opens.
     */
    public function updateSettings(Request $request)
    {
        Setting::setBool('heatmap_enabled', $request->boolean('heatmap_enabled'));
        Setting::setBool('heatmap_auto', $request->boolean('heatmap_auto'));

        AuditLog::record(
            $request->user(),
            'Changed public heatmap settings',
            null,
            'Heatmap '.($request->boolean('heatmap_enabled') ? 'ON' : 'OFF').', automatic '.($request->boolean('heatmap_auto') ? 'ON' : 'OFF')
        );

        return back()->with('status', 'Heatmap settings saved. They apply to the public map immediately.');
    }
}
