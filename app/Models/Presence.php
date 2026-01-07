<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Presence extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'enhancer',
        'company',
        'position',
        'fingerprint',
        'time',
        'piece',
        'price',
        'late',
        'earlier',
        'type',
        'category',
        'coordinate',
        'biometric',
    ];

    protected $casts = [
        'time' => 'datetime',
        'piece' => 'decimal:2',
        'price' => 'integer',
        'late' => 'integer',
        'earlier' => 'integer',
    ];

    /**
     * Check if this is a check-in
     */
    public function isCheckIn(): bool
    {
        return $this->type === '0';
    }

    /**
     * Check if this is a check-out
     */
    public function isCheckOut(): bool
    {
        return $this->type === '1';
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type === '0' ? 'Check In' : 'Check Out';
    }
}
