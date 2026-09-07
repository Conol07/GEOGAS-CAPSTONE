<?php

namespace App\Http\Controllers\Lgu;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\GasolineStation;
use App\Models\StationPersonnel;
use App\Models\StationService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StationController extends Controller
{
    public function index(Request $request)
    {
        $stations = GasolineStation::search($request->input('q'))
            ->inBarangay($request->input('barangay'))
            ->orderBy('station_name')
            ->paginate(12)
            ->withQueryString();

        $barangays = GasolineStation::select('barangay')->distinct()->orderBy('barangay')->pluck('barangay');

        return view('lgu.stations.index', compact('stations', 'barangays'));
    }

    public function create()
    {
        return view('lgu.stations.create');
    }

    /**
     * Registers the station AND its manager account in one step, since a
     * station is useless to the public without someone able to update it.
     */
    public function store(Request $request)
    {
        $validated = $this->validateStation($request);

        $managerData = $request->validate([
            'manager_email' => ['required', 'email', 'unique:users,email'],
            'manager_username' => ['required', 'string', 'max:255'],
            'manager_password' => ['required', 'string', 'min:8'],
        ]);

        $station = GasolineStation::create($validated);

        foreach (StationService::CATALOG as $key => $label) {
            $station->services()->create([
                'service_key' => $key,
                'label' => $label,
                'available' => $request->boolean("service_{$key}"),
            ]);
        }

        $manager = User::create([
            'name' => $managerData['manager_username'],
            'email' => $managerData['manager_email'],
            'password' => Hash::make($managerData['manager_password']),
            'role' => 'manager',
            'status' => 'active',
        ]);

        StationPersonnel::create([
            'user_id' => $manager->id,
            'station_id' => $station->id,
            'added_by' => $request->user()->id,
        ]);

        AuditLog::record($request->user(), 'Registered gasoline station', $station, $station->station_name);

        return redirect()->route('lgu.stations.index')->with('status', 'Station and manager account created. It now appears on the public map.');
    }

    public function edit(GasolineStation $station)
    {
        return view('lgu.stations.edit', compact('station'));
    }

    public function update(Request $request, GasolineStation $station)
    {
        $validated = $this->validateStation($request);

        $station->update($validated);

        AuditLog::record($request->user(), 'Updated station information', $station, $station->station_name);

        return redirect()->route('lgu.stations.index')->with('status', 'Station updated successfully.');
    }

    public function destroy(Request $request, GasolineStation $station)
    {
        $station->update(['status' => 'inactive']);

        AuditLog::record($request->user(), 'Deactivated station', $station, $station->station_name);

        return back()->with('status', 'Station deactivated.');
    }

    private function validateStation(Request $request): array
    {
        return $request->validate([
            'station_name' => ['required', 'string', 'max:255'],
            'company_owner' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
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
