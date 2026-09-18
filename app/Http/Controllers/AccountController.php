<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SessionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Account Settings — available to any authenticated role (LGU admin,
 * station manager, station staff). Profile info + password management
 * live on one page instead of a standalone "Change Password" nav item.
 */
class AccountController extends Controller
{
    public function edit()
    {
        return view('account.settings');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($validated);

        AuditLog::record($user, 'Updated profile information', $user);
        SessionLog::record($request, $user, 'account_settings_changed', 'Updated profile information');

        return back()->with('status', 'Profile information updated.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditLog::record($request->user(), 'Changed own password', $request->user());
        SessionLog::record($request, $request->user(), 'account_settings_changed', 'Changed password');

        return back()->with('status', 'Password updated successfully.');
    }
}
