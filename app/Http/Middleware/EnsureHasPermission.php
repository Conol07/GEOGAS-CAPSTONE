<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fine-grained access for station staff, e.g. ->middleware('permission:update_prices').
 * Managers and LGU admins always pass; staff must have the key in their
 * station_personnel.permissions array (see StationPersonnel::PERMISSIONS).
 */
class EnsureHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasStationPermission($permission)) {
            abort(403, 'Your account does not have permission to perform this action.');
        }

        return $next($request);
    }
}
