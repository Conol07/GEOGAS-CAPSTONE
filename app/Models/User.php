<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'status',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStationPersonnel(): bool
    {
        return $this->role === 'station_personnel';
    }

    public function stationAssignment()
    {
        return $this->hasOne(StationPersonnel::class);
    }

    // Convenience accessor: the station this user manages (null for admins)
    public function station()
    {
        return $this->hasOneThrough(
            GasolineStation::class,
            StationPersonnel::class,
            'user_id',
            'id',
            'id',
            'station_id'
        );
    }

    public function submittedFuelPrices()
    {
        return $this->hasMany(FuelPrice::class, 'submitted_by');
    }

    public function verifiedFuelPrices()
    {
        return $this->hasMany(FuelPrice::class, 'verified_by');
    }
}
