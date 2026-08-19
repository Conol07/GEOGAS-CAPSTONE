<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GasolineStation;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function index(Request $request)
    {
        $stations = GasolineStation::search($request->input('q'))
            ->orderBy('station_name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.stations.index', compact('stations'));
    }

    public function create()
    {
        return view('admin.stations.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateStation($request);

        GasolineStation::create($validated);

        return redirect()->route('admin.stations.index')->with('status', 'Station registered successfully.');
    }

    public function edit(GasolineStation $station)
    {
        return view('admin.stations.edit', compact('station'));
    }

    public function update(Request $request, GasolineStation $station)
    {
        $validated = $this->validateStation($request);

        $station->update($validated);

        return redirect()->route('admin.stations.index')->with('status', 'Station updated successfully.');
    }

    public function destroy(GasolineStation $station)
    {
        $station->update(['status' => 'inactive']);

        return back()->with('status', 'Station deactivated.');
    }

    private function validateStation(Request $request): array
    {
        return $request->validate([
            'station_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:100'],
            'municipality' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
