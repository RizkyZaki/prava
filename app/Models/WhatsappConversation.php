<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappConversation extends Model
{
    protected $fillable = [
        'phone',
        'customer_name',
        'whatsapp_phone_number_id',
        'mode',
        'assigned_to',
        'last_message_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('mode', 'admin')->whereNull('ended_at');
    }

    public function scopeNeedsAttention(Builder $query): Builder
    {
        return $query->where('mode', 'admin')->whereNull('assigned_to')->whereNull('ended_at');
    }
}
