<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StationPersonnel extends Model
{
    use HasFactory;

    protected $table = 'station_personnel';

    protected $fillable = [
        'user_id', 'station_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function station()
    {
        return $this->belongsTo(GasolineStation::class, 'station_id');
    }
}
