<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'category', 'content', 'image_path', 'status', 'published_at',
        'related_station_id', 'related_barangay', 'related_fuel_type_id',
        'previous_price', 'current_price', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'previous_price' => 'decimal:2',
            'current_price' => 'decimal:2',
        ];
    }

    public const CATEGORIES = [
        'price_update' => 'Price Update',
        'price_hike' => 'Price Hike',
        'price_decrease' => 'Price Decrease',
        'new_station' => 'New Station',
        'station_update' => 'Station Update',
        'fuel_availability' => 'Fuel Availability',
        'lgu_announcement' => 'LGU Announcement',
        'important_notice' => 'Important Notice',
        'other' => 'Other',
    ];

    public function station()
    {
        return $this->belongsTo(GasolineStation::class, 'related_station_id');
    }

    public function fuelType()
    {
        return $this->belongsTo(FuelType::class, 'related_fuel_type_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    public function priceChange(): ?float
    {
        if ($this->previous_price === null || $this->current_price === null) {
            return null;
        }

        return round((float) $this->current_price - (float) $this->previous_price, 2);
    }
}
