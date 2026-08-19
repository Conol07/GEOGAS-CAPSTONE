<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FuelPrice;
use Illuminate\Http\Request;

class FuelPriceVerificationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $updates = FuelPrice::with(['station', 'submitter'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.verification.index', compact('updates', 'status'));
    }

    public function approve(Request $request, FuelPrice $fuelPrice)
    {
        abort_if($fuelPrice->status !== 'pending', 400, 'This update has already been processed.');

        $fuelPrice->update([
            'status' => 'approved',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('status', 'Fuel price approved and published.');
    }

    public function reject(Request $request, FuelPrice $fuelPrice)
    {
        abort_if($fuelPrice->status !== 'pending', 400, 'This update has already been processed.');

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $fuelPrice->update([
            'status' => 'rejected',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return back()->with('status', 'Fuel price update rejected.');
    }
}
