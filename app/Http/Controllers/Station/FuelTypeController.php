<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FuelPrice;
use App\Models\FuelType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Lets a station manager add a fuel type their station offers that isn't
 * already in the system-wide catalog (e.g. E10). New fuel types are never
 * "temporary" — they're a normal FuelType row, so every view that already
 * iterates FuelType::ordered() picks them up automatically.
 */
class FuelTypeController extends Controller
{
    public function create(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $fuelTypes = FuelType::ordered()->get();

        return view('station.fuel-type-create', compact('fuelTypes'));
    }

    public function store(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'specification' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'initial_price' => ['required', 'numeric', 'min:0'],
            'availability_status' => ['required', 'in:no_fuel,almost_empty,enough'],
        ]);

        // Reuse an existing fuel type with the same name instead of duplicating it.
        $fuelType = FuelType::where('name', $validated['name'])->first();

        if (! $fuelType) {
            $fuelType = FuelType::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']).'-'.Str::random(4),
                'sort_order' => (FuelType::max('sort_order') ?? 0) + 1,
                'specification' => $validated['specification'] ?? null,
                'description' => $validated['description'] ?? null,
                'created_by' => $request->user()->id,
            ]);
        }

        FuelPrice::create([
            'station_id' => $assignment->station_id,
            'fuel_type_id' => $fuelType->id,
            'price' => $validated['initial_price'],
            'availability_status' => $validated['availability_status'],
            'updated_by' => $request->user()->id,
        ]);

        AuditLog::record($request->user(), 'Added fuel type', $fuelType, "{$fuelType->name} for station #{$assignment->station_id}");

        return redirect()->route('station.prices.edit')->with('status', "{$fuelType->name} added and is now part of your station's fuel lineup.");
    }
}
