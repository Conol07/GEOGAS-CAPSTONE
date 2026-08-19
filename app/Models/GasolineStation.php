<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasolineStation extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_name', 'address', 'barangay', 'municipality', 'province',
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

    public function fuelPrices()
    {
        return $this->hasMany(FuelPrice::class, 'station_id');
    }

    // Latest publicly-visible (approved) price
    public function latestApprovedPrice()
    {
        return $this->hasOne(FuelPrice::class, 'station_id')
            ->where('status', 'approved')
            ->latest('effective_date')
            ->latest('verified_at');
    }

    public function pendingPrices()
    {
        return $this->hasMany(FuelPrice::class, 'station_id')->where('status', 'pending');
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
}
