<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceData extends Model
{
    use HasFactory;

    protected $table = 'face_data';

    protected $fillable = [
        'user_id',
        'face_image',
        'face_embedding',
        'status',
        'registered_at',
    ];

    protected $casts = [
        'face_embedding' => 'json',
        'registered_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * Relationship ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk mendapatkan face data yang active
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get full path ke face image
     */
    public function getFaceImagePath(): string
    {
        return storage_path('app/public/' . $this->face_image);
    }

    /**
     * Get URL untuk akses face image (untuk preview)
     */
    public function getFaceImageUrl(): string
    {
        return asset('storage/' . $this->face_image);
    }
}
