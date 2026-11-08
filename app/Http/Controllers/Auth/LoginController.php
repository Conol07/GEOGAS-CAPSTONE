<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SessionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = Str::lower($credentials['email']).'|'.$request->ip();
        $max = config('geogas.login_max_attempts');

        if (RateLimiter::tooManyAttempts($throttleKey, $max)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            SessionLog::record($request, null, 'failed_login', 'Locked out: too many attempts', $credentials['email']);

            throw ValidationException::withMessages([
                'email' => 'Too many failed login attempts. Please try again in '.ceil($seconds / 60).' minute(s).',
            ])->status(429);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, config('geogas.login_lockout_seconds'));
            SessionLog::record($request, null, 'failed_login', 'Invalid credentials', $credentials['email']);

            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->status !== 'active') {
            SessionLog::record($request, $user, 'failed_login', 'Account is deactivated', $credentials['email']);
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Contact the administrator.',
            ]);
        }

        SessionLog::record($request, $user, 'login');

        return $user->isLguAdmin()
            ? redirect()->intended(route('lgu.dashboard'))
            : redirect()->intended(route('station.dashboard'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        SessionLog::record($request, $user, 'logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
