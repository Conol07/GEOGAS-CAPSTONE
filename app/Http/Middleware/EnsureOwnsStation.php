<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guarantees station personnel can only act on their own assigned station.
 * Expects a {station} route parameter (id) or falls back to the
 * user's own assignment when none is given.
 */
class EnsureOwnsStation
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $next($request); // admins may act on any station
        }

        $assignment = $user->stationAssignment;

        if (! $assignment) {
            abort(403, 'No station is assigned to your account.');
        }

        $stationParam = $request->route('station');
        $stationId = is_object($stationParam) ? $stationParam->id : $stationParam;

        if ($stationId !== null && (int) $stationId !== (int) $assignment->station_id) {
            abort(403, 'You may only manage your own assigned station.');
        }

        // Make the resolved station id available to controllers
        $request->attributes->set('resolved_station_id', $assignment->station_id);

        return $next($request);
    }
}
