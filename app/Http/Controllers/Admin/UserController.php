<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GasolineStation;
use App\Models\StationPersonnel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('stationAssignment.station')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $stations = GasolineStation::active()->orderBy('station_name')->get();

        return view('admin.users.create', compact('stations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,station_personnel'],
            'status' => ['required', 'in:active,inactive'],
            'station_id' => ['nullable', 'required_if:role,station_personnel', 'exists:gasoline_stations,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => $validated['status'],
        ]);

        if ($validated['role'] === 'station_personnel') {
            StationPersonnel::create([
                'user_id' => $user->id,
                'station_id' => $validated['station_id'],
            ]);
        }

        return redirect()->route('admin.users.index')->with('status', 'User account created.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

        return back()->with('status', 'User status updated.');
    }
}
