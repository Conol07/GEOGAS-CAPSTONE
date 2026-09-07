<?php

namespace App\Http\Controllers\Lgu;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Read/monitor-oriented: station manager accounts are created together
 * with their station (see Lgu\StationController::store), and staff
 * accounts are created by their own manager (see Station\StaffController).
 * The LGU admin can still view everyone and deactivate accounts.
 */
class UserController extends Controller
{
    public function index()
    {
        $users = User::with('stationAssignment.station')
            ->orderByRaw("FIELD(role, 'lgu_admin', 'manager', 'staff')")
            ->orderBy('name')
            ->paginate(20);

        return view('lgu.users.index', compact('users'));
    }

    public function toggleStatus(Request $request, User $user)
    {
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

        AuditLog::record($request->user(), 'Toggled user status', $user, "{$user->name} is now {$user->status}");

        return back()->with('status', 'User status updated.');
    }
}
