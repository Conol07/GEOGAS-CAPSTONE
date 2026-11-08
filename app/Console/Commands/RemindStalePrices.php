<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\GasolineStation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class RemindStalePrices extends Command
{
    protected $signature = 'geogas:remind-stale {--dry-run : List overdue stations without sending anything}';

    protected $description = 'Remind station managers whose fuel prices have not been updated recently';

    public function handle(): int
    {
        $hours = config('geogas.reminder_hours');
        $cutoff = now()->subHours($hours);
        $sent = 0;

        GasolineStation::active()->with('personnel.user')->get()->each(function ($station) use ($cutoff, $hours, &$sent) {
            $last = $station->lastPriceUpdate();
            if ($last && $last->gte($cutoff)) {
                return;
            }

            $managers = $station->personnel->map->user->filter(fn ($u) => $u && $u->role === 'manager' && $u->status === 'active' && $u->email);
            $when = $last ? $last->diffForHumans() : 'never';
            $this->line("{$station->station_name}: last update {$when}".($managers->isEmpty() ? ' (no active manager)' : ''));

            if ($this->option('dry-run')) {
                return;
            }

            // Don't nag: at most one reminder per station per day.
            $already = AuditLog::where('action', 'Sent stale-price reminder')
                ->where('subject_type', GasolineStation::class)->where('subject_id', $station->id)
                ->where('created_at', '>=', now()->subDay())->exists();
            if ($already) {
                return;
            }

            foreach ($managers as $m) {
                Mail::raw(
                    "Hello {$m->name},\n\nThe fuel prices for {$station->station_name} on GeoGas ManFort were last updated {$when}. ".
                    "Please log in and update them, or press \"Prices unchanged — confirm\" if nothing has changed. ".
                    "Prices older than ".config('geogas.stale_hours')." hours are shown to the public as possibly outdated.\n\nGeoGas ManFort",
                    fn ($msg) => $msg->to($m->email)->subject('Reminder: update fuel prices for '.$station->station_name)
                );
                $sent++;
            }

            AuditLog::record(null, 'Sent stale-price reminder', $station, "Last update {$when}; {$managers->count()} manager(s) emailed");
        });

        $this->info($this->option('dry-run') ? 'Dry run complete.' : "Reminders sent: {$sent}");

        return self::SUCCESS;
    }
}
