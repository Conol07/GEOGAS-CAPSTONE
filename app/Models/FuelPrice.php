<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id', 'fuel_type_id', 'price', 'availability_status', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function station()
    {
        return $this->belongsTo(GasolineStation::class, 'station_id');
    }

    public function fuelType()
    {
        return $this->belongsTo(FuelType::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * The record immediately preceding this one for the same
     * station+fuel type — used to compute price-change indicators.
     */
    public function previous(): ?self
    {
        return static::where('station_id', $this->station_id)
            ->where('fuel_type_id', $this->fuel_type_id)
            ->where('id', '<', $this->id)
            ->latest('id')
            ->first();
    }

    public function priceChangeDirection(): string
    {
        $prev = $this->previous();

        if (! $prev) {
            return 'none';
        }

        if ((float) $this->price > (float) $prev->price) {
            return 'increased';
        }

        if ((float) $this->price < (float) $prev->price) {
            return 'decreased';
        }

        return 'unchanged';
    }

    public function isStale(int $hours = 72): bool
    {
        return $this->created_at->lt(now()->subHours($hours));
    }
}
