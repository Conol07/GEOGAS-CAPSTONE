<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SessionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'email_attempted', 'role', 'action', 'description', 'ip_address', 'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A short, human-readable browser/OS summary parsed from the raw user
     * agent string — no external package required.
     */
    public function device(): string
    {
        $ua = $this->user_agent ?? '';

        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') && ! str_contains($ua, 'Chrome') => 'Safari',
            default => 'Unknown Browser',
        };

        $os = match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Unknown Device',
        };

        return "{$browser} / {$os}";
    }

    public static function record(Request $request, ?User $user, string $action, ?string $description = null, ?string $emailAttempted = null): void
    {
        static::create([
            'user_id' => $user?->id,
            'email_attempted' => $emailAttempted,
            'role' => $user?->role,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }
}
