<?php

namespace App\Http\Controllers\StationPersonnel;

use App\Http\Controllers\Controller;
use App\Models\FuelPrice;
use Illuminate\Http\Request;

class FuelPriceController extends Controller
{
    /**
     * Show the form to submit a new price update for the personnel's own station.
     */
    public function create(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $station = $assignment->station;

        return view('station.fuel-price-form', compact('station'));
    }

    public function store(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $validated = $request->validate([
            'gasoline_price' => ['nullable', 'numeric', 'min:0'],
            'diesel_price' => ['nullable', 'numeric', 'min:0'],
            'premium_price' => ['nullable', 'numeric', 'min:0'],
            'regular_price' => ['nullable', 'numeric', 'min:0'],
            'effective_date' => ['required', 'date'],
        ]);

        if (! array_filter([
            $validated['gasoline_price'] ?? null,
            $validated['diesel_price'] ?? null,
            $validated['premium_price'] ?? null,
            $validated['regular_price'] ?? null,
        ])) {
            return back()->withErrors(['gasoline_price' => 'Provide at least one fuel price.'])->withInput();
        }

        FuelPrice::create([
            ...$validated,
            'station_id' => $assignment->station_id,
            'status' => 'pending',
            'submitted_by' => $request->user()->id,
        ]);

        return redirect()->route('station.dashboard')
            ->with('status', 'Fuel price submitted and is pending administrator verification.');
    }

    /**
     * Update history for the personnel's own station only.
     */
    public function history(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $updates = FuelPrice::where('station_id', $assignment->station_id)
            ->latest('created_at')
            ->paginate(15);

        return view('station.history', compact('updates'));
    }
}
