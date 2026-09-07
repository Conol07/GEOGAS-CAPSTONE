<?php

namespace App\Models;

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

    public function isLguAdmin(): bool
    {
        return $this->role === 'lgu_admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function stationAssignment()
    {
        return $this->hasOne(StationPersonnel::class);
    }

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

    public function fuelPriceUpdates()
    {
        return $this->hasMany(FuelPrice::class, 'updated_by');
    }

    /**
     * Managers have full access to their own station. Staff are limited
     * to whatever keys are in their station_personnel.permissions array.
     */
    public function hasStationPermission(string $key): bool
    {
        if ($this->isManager() || $this->isLguAdmin()) {
            return true;
        }

        $assignment = $this->stationAssignment;

        return $assignment && in_array($key, $assignment->permissions ?? [], true);
    }
}
