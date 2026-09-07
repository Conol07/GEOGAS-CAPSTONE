<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StationPersonnel extends Model
{
    use HasFactory;

    protected $table = 'station_personnel';

    protected $fillable = [
        'user_id', 'station_id', 'added_by', 'permissions',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function station()
    {
        return $this->belongsTo(GasolineStation::class, 'station_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public const PERMISSIONS = [
        'view_dashboard' => 'View dashboard',
        'update_prices' => 'Update fuel prices',
        'update_availability' => 'Update fuel availability',
        'view_price_history' => 'View price history',
        'manage_services' => 'Manage station services',
        'view_reports' => 'View reports',
        'generate_reports' => 'Generate reports',
        'edit_station_info' => 'Edit station information',
    ];
}
