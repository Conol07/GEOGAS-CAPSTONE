<?php

namespace App\Http\Controllers\Lgu;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\GasolineStation;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $complaints = Complaint::with('station')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->filled('station_id'), fn ($q) => $q->where('station_id', $request->input('station_id')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->input('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('reference_no', 'like', "%{$term}%")
                        ->orWhere('subject', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%");
                });
            })
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $stations = GasolineStation::orderBy('station_name')->get(['id', 'station_name']);

        return view('lgu.complaints.index', compact('complaints', 'stations'));
    }

    public function show(Complaint $complaint)
    {
        $complaint->load('station', 'handler');

        return view('lgu.complaints.show', compact('complaint'));
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,under_review,resolved'],
            'lgu_response' => ['nullable', 'string', 'max:2000'],
        ]);

        $complaint->status = $validated['status'];
        $complaint->lgu_response = $validated['lgu_response'] ?? $complaint->lgu_response;
        $complaint->handled_by = $request->user()->id;

        if ($validated['status'] === 'under_review' && ! $complaint->reviewed_at) {
            $complaint->reviewed_at = now();
        }
        if ($validated['status'] === 'resolved') {
            $complaint->resolved_at = now();
        }

        $complaint->save();

        AuditLog::record($request->user(), 'Updated complaint status', $complaint, "{$complaint->reference_no} → {$complaint->status}");

        return back()->with('status', 'Complaint updated.');
    }
}
