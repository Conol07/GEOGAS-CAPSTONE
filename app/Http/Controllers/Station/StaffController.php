<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\StationPersonnel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Managers create/manage staff for THEIR station only. Staff can never
 * belong to another station, and only a manager (not staff) may reach
 * these routes — enforced by the 'role:manager' middleware in web.php.
 */
class StaffController extends Controller
{
    public function index(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $staff = StationPersonnel::with('user')
            ->where('station_id', $assignment->station_id)
            ->whereHas('user', fn ($q) => $q->where('role', 'staff'))
            ->get();

        return view('station.staff.index', compact('staff'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->stationAssignment, 403);

        return view('station.staff.create', ['permissions' => StationPersonnel::PERMISSIONS]);
    }

    public function store(Request $request)
    {
        $assignment = $request->user()->stationAssignment;
        abort_unless($assignment, 403, 'No station is assigned to your account.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'permissions' => ['array'],
            'permissions.*' => ['in:'.implode(',', array_keys(StationPersonnel::PERMISSIONS))],
        ]);

        $staffUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'staff',
            'status' => 'active',
        ]);

        StationPersonnel::create([
            'user_id' => $staffUser->id,
            'station_id' => $assignment->station_id,
            'added_by' => $request->user()->id,
            'permissions' => $validated['permissions'] ?? [],
        ]);

        AuditLog::record($request->user(), 'Created staff account', $staffUser, $staffUser->name);

        return redirect()->route('station.staff.index')->with('status', 'Staff account created.');
    }

    public function edit(Request $request, User $staff)
    {
        $this->authorizeStaff($request, $staff);

        return view('station.staff.edit', [
            'staffUser' => $staff,
            'assignment' => $staff->stationAssignment,
            'permissions' => StationPersonnel::PERMISSIONS,
        ]);
    }

    public function update(Request $request, User $staff)
    {
        $this->authorizeStaff($request, $staff);

        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['in:'.implode(',', array_keys(StationPersonnel::PERMISSIONS))],
        ]);

        $staff->stationAssignment->update(['permissions' => $validated['permissions'] ?? []]);

        AuditLog::record($request->user(), 'Updated staff permissions', $staff, $staff->name);

        return redirect()->route('station.staff.index')->with('status', 'Staff permissions updated.');
    }

    public function toggleStatus(Request $request, User $staff)
    {
        $this->authorizeStaff($request, $staff);

        $staff->update(['status' => $staff->status === 'active' ? 'inactive' : 'active']);

        AuditLog::record($request->user(), 'Toggled staff status', $staff, "{$staff->name} is now {$staff->status}");

        return back()->with('status', 'Staff status updated.');
    }

    public function resetPassword(Request $request, User $staff)
    {
        $this->authorizeStaff($request, $staff);

        $temp = Str::password(10);
        $staff->update(['password' => Hash::make($temp)]);

        AuditLog::record($request->user(), 'Reset staff password', $staff, $staff->name);

        return back()->with('status', "Password reset. Temporary password: {$temp}");
    }

    public function destroy(Request $request, User $staff)
    {
        $this->authorizeStaff($request, $staff);

        $staff->stationAssignment?->delete();
        $staff->delete();

        AuditLog::record($request->user(), 'Deleted staff account', null, $staff->name);

        return redirect()->route('station.staff.index')->with('status', 'Staff account removed.');
    }

    private function authorizeStaff(Request $request, User $staff): void
    {
        $assignment = $request->user()->stationAssignment;
        $staffAssignment = $staff->stationAssignment;

        abort_if(
            ! $assignment || ! $staffAssignment || $staff->role !== 'staff'
                || $staffAssignment->station_id !== $assignment->station_id,
            403,
            'You may only manage staff belonging to your own station.'
        );
    }
}
