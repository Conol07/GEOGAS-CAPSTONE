<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FuelPrice;
use App\Models\FuelType;
use Illuminate\Http\Request;

class FuelPriceController extends Controller
{
    /**
     * Direct-update workflow — no LGU approval. Every submission inserts
     * a new row (see FuelPrice migration), which doubles as price history.
     */
    public function edit(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $station = $assignment->station;
        $fuelTypes = FuelType::ordered()->get();
        $currentPrices = $station->currentPrices();

        return view('station.fuel-price-form', compact('station', 'fuelTypes', 'currentPrices'));
    }

    public function update(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $validated = $request->validate([
            'prices' => ['required', 'array'],
            'prices.*.price' => ['nullable', 'numeric', 'min:0'],
            'prices.*.availability_status' => ['required_with:prices.*.price', 'in:no_fuel,almost_empty,enough'],
        ]);

        $updatedCount = 0;

        foreach ($validated['prices'] as $fuelTypeId => $data) {
            if (! isset($data['price']) || $data['price'] === '') {
                continue;
            }

            FuelPrice::create([
                'station_id' => $assignment->station_id,
                'fuel_type_id' => $fuelTypeId,
                'price' => $data['price'],
                'availability_status' => $data['availability_status'] ?? 'enough',
                'updated_by' => $request->user()->id,
            ]);
            $updatedCount++;
        }

        if ($updatedCount === 0) {
            return back()->withErrors(['prices' => 'Enter at least one fuel price to update.'])->withInput();
        }

        AuditLog::record($request->user(), 'Updated fuel prices/availability', null, "Station #{$assignment->station_id}, {$updatedCount} fuel type(s)");

        return redirect()->route('station.dashboard')->with('status', 'Fuel prices and availability updated. Changes are live on the public map immediately.');
    }

    public function history(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $updates = FuelPrice::with('fuelType', 'updater')
            ->where('station_id', $assignment->station_id)
            ->latest('created_at')
            ->paginate(20);

        return view('station.history', compact('updates'));
    }
}
