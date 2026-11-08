<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Self-service editing of a station's own basic info by its manager.
 * Deliberately scoped to contact details, description, logo, and photo —
 * station name, address, barangay, and map coordinates stay LGU-controlled
 * since they affect the public map/location picker and registration record.
 */
class StationInfoController extends Controller
{
    public function edit(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $station = $assignment->station;

        return view('station.info-edit', compact('station'));
    }

    public function update(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $station = $assignment->station;

        $validated = $request->validate([
            'contact_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        $update = [
            'contact_number' => $validated['contact_number'] ?? null,
            'email' => $validated['email'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('logo')) {
            if ($station->logo_path) {
                Storage::disk('public')->delete($station->logo_path);
            }
            $update['logo_path'] = $request->file('logo')->store('stations/logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            if ($station->logo_path) {
                Storage::disk('public')->delete($station->logo_path);
            }
            $update['logo_path'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($station->photo_path) {
                Storage::disk('public')->delete($station->photo_path);
            }
            $update['photo_path'] = $request->file('photo')->store('stations/photos', 'public');
        } elseif ($request->boolean('remove_photo')) {
            if ($station->photo_path) {
                Storage::disk('public')->delete($station->photo_path);
            }
            $update['photo_path'] = null;
        }

        $station->update($update);

        AuditLog::record($request->user(), 'Updated station information', $station, $station->station_name);

        return back()->with('status', 'Station information updated.');
    }
}
