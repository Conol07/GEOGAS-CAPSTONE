<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use App\Models\SessionLog;
use App\Models\StationPersonnel;
use Illuminate\Http\Request;

/**
 * Managers see their own session logs plus their station's staff logs.
 * Staff see only their own — never another account's, even on the same
 * station, unless the existing authorization system grants it (it doesn't).
 */
class SessionLogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isManager()) {
            $stationUserIds = StationPersonnel::where('station_id', $user->stationAssignment->station_id)
                ->pluck('user_id');
        } else {
            $stationUserIds = collect([$user->id]);
        }

        $logs = SessionLog::with('user')
            ->whereIn('user_id', $stationUserIds)
            ->when($request->filled('user_id') && $stationUserIds->contains($request->input('user_id')), fn ($q) => $q->where('user_id', $request->input('user_id')))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        $staffOptions = $user->isManager()
            ? \App\Models\User::whereIn('id', $stationUserIds)->orderBy('name')->get(['id', 'name', 'role'])
            : collect();

        return view('station.session-logs', compact('logs', 'staffOptions'));
    }
}
