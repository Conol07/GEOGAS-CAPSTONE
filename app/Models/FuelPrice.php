<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id', 'gasoline_price', 'diesel_price', 'premium_price', 'regular_price',
        'effective_date', 'status', 'submitted_by', 'verified_by', 'verified_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'verified_at' => 'datetime',
            'gasoline_price' => 'decimal:2',
            'diesel_price' => 'decimal:2',
            'premium_price' => 'decimal:2',
            'regular_price' => 'decimal:2',
        ];
    }

    public function station()
    {
        return $this->belongsTo(GasolineStation::class, 'station_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
