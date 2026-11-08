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

        $min = config('geogas.price_min');
        $max = config('geogas.price_max');

        $validated = $request->validate([
            'prices' => ['required', 'array'],
            'prices.*.price' => ['nullable', 'numeric', "between:{$min},{$max}"],
            'prices.*.availability_status' => ['required_with:prices.*.price', 'in:no_fuel,almost_empty,enough'],
        ], [
            'prices.*.price.between' => "A fuel price must be between ₱{$min} and ₱{$max}. Please check for a typing error.",
            'prices.*.price.numeric' => 'Prices must be numbers.',
        ]);

        // Large jumps vs the current price need an explicit confirmation —
        // catches a slipped decimal or an extra digit before it goes live.
        $current = $assignment->station->currentPrices();
        $threshold = config('geogas.price_jump_percent');
        $jumps = [];
        foreach ($validated['prices'] as $fuelTypeId => $data) {
            if (! isset($data['price']) || $data['price'] === '') {
                continue;
            }
            $old = $current->get($fuelTypeId);
            if ($old && (float) $old->price > 0) {
                $pct = (((float) $data['price']) - (float) $old->price) / (float) $old->price * 100;
                if (abs($pct) > $threshold) {
                    $jumps[] = [
                        'fuel' => optional(FuelType::find($fuelTypeId))->name ?? 'Fuel',
                        'old' => (float) $old->price,
                        'new' => (float) $data['price'],
                        'pct' => round($pct, 1),
                    ];
                }
            }
        }

        if ($jumps && ! $request->boolean('confirm_jump')) {
            return back()->withInput()->with('price_jumps', $jumps);
        }

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

    /**
     * "Prices unchanged" — records a fresh timestamp for the current prices
     * without retyping them, so a stable price isn't flagged as outdated.
     * Append-only like every other update, so it appears in history.
     */
    public function confirm(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $current = $assignment->station->currentPrices();
        abort_if($current->isEmpty(), 422, 'There are no prices to confirm yet.');

        foreach ($current as $p) {
            FuelPrice::create([
                'station_id' => $assignment->station_id,
                'fuel_type_id' => $p->fuel_type_id,
                'price' => $p->price,
                'availability_status' => $p->availability_status,
                'updated_by' => $request->user()->id,
            ]);
        }

        AuditLog::record($request->user(), 'Confirmed prices unchanged', null, "Station #{$assignment->station_id}, {$current->count()} fuel type(s)");

        return back()->with('status', 'Prices confirmed as unchanged. The “last updated” time is now current.');
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
