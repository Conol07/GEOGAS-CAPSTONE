<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no', 'name', 'contact', 'category', 'station_id', 'subject', 'description',
        'photo_path', 'status', 'lgu_response', 'handled_by', 'reviewed_at', 'resolved_at', 'submitter_ip',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function station()
    {
        return $this->belongsTo(GasolineStation::class, 'station_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public static function generateReferenceNo(): string
    {
        do {
            $ref = 'GG-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        } while (static::where('reference_no', $ref)->exists());

        return $ref;
    }

    public const CATEGORIES = [
        'incorrect_price' => 'Incorrect fuel price',
        'incorrect_availability' => 'Incorrect fuel availability',
        'incorrect_station_info' => 'Station information is incorrect',
        'price_not_updated' => 'Price not updated',
        'fuel_unavailable_despite_shown' => 'Fuel unavailable despite showing available',
        'other' => 'Other',
    ];
}
