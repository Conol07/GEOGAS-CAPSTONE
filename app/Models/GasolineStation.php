<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasolineStation extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_name', 'company_owner', 'email', 'address', 'barangay', 'municipality', 'province',
        'latitude', 'longitude', 'contact_number', 'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function personnel()
    {
        return $this->hasMany(StationPersonnel::class, 'station_id');
    }

    public function manager()
    {
        return $this->hasOneThrough(User::class, StationPersonnel::class, 'station_id', 'id', 'id', 'user_id')
            ->where('users.role', 'manager');
    }

    public function fuelPrices()
    {
        return $this->hasMany(FuelPrice::class, 'station_id');
    }

    public function services()
    {
        return $this->hasMany(StationService::class, 'station_id');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'station_id');
    }

    /**
     * The latest price+availability row per fuel type for this station.
     * Loaded on demand via GasolineStation::withCurrentPrices() scope below,
     * or call ->currentPrices() directly.
     */
    public function currentPrices()
    {
        return FuelPrice::where('station_id', $this->id)
            ->select('fuel_prices.*')
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')
                    ->from('fuel_prices')
                    ->where('station_id', $this->id)
                    ->groupBy('fuel_type_id');
            })
            ->with('fuelType')
            ->get()
            ->keyBy('fuel_type_id');
    }

    public function overallAvailability(): string
    {
        $prices = $this->currentPrices();

        if ($prices->isEmpty()) {
            return 'unknown';
        }

        if ($prices->contains(fn ($p) => $p->availability_status === 'enough')) {
            return 'enough';
        }

        if ($prices->contains(fn ($p) => $p->availability_status === 'almost_empty')) {
            return 'almost_empty';
        }

        return 'no_fuel';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('station_name', 'like', "%{$term}%")
                ->orWhere('address', 'like', "%{$term}%")
                ->orWhere('barangay', 'like', "%{$term}%");
        });
    }

    public function scopeInBarangay($query, ?string $barangay)
    {
        if (! $barangay) {
            return $query;
        }

        return $query->where('barangay', $barangay);
    }
}
