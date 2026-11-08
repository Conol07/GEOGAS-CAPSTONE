<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StationService extends Model
{
    use HasFactory;

    protected $fillable = ['station_id', 'service_key', 'label', 'available'];

    protected function casts(): array
    {
        return ['available' => 'boolean'];
    }

    public function station()
    {
        return $this->belongsTo(GasolineStation::class, 'station_id');
    }

    public const CATALOG = [
        'store' => 'Convenience Store',
        'mechanic' => 'Mechanic / Service',
        'motor_oil' => 'Motor Oil',
        'car_oil' => 'Car Oil',
        'cr' => 'Public CR / Restroom',
        'air_pump' => 'Air Pump',
        'tire_repair' => 'Tire Repair',
        'car_wash' => 'Car Wash',
        'motorcycle_service' => 'Motorcycle Service',
        'battery_service' => 'Battery Service',
        'lubricants' => 'Lubricants',
        'atm' => 'ATM',
        'food_drinks' => 'Food / Drinks',
        'parking' => 'Parking',
    ];
}
