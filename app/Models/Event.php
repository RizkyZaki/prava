<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'type',
        'color',
        'all_day',
        'created_by',
        'participants',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'all_day' => 'boolean',
        'participants' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($event) {
            if (empty($event->created_by) && Auth::id()) {
                $event->created_by = Auth::id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get participants as User collection
     */
    public function getParticipantsUsersAttribute()
    {
        if (empty($this->participants)) {
            return collect();
        }

        return User::whereIn('id', $this->participants)->get();
    }

    /**
     * Check if event is ongoing
     */
    public function isOngoing(): bool
    {
        $now = Carbon::now();
        return $this->start_date <= $now && ($this->end_date ? $this->end_date >= $now : true);
    }

    /**
     * Check if event is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->start_date > Carbon::now();
    }

    /**
     * Check if event is past
     */
    public function isPast(): bool
    {
        $endDate = $this->end_date ?? $this->start_date;
        return $endDate < Carbon::now();
    }

    /**
     * Get formatted date range
     */
    public function getFormattedDateRangeAttribute(): string
    {
        $start = $this->start_date->format('d M Y');

        if ($this->all_day) {
            if ($this->end_date && !$this->start_date->isSameDay($this->end_date)) {
                $end = $this->end_date->format('d M Y');
                return "{$start} - {$end}";
            }
            return $start;
        }

        $startTime = $this->start_date->format('d M Y H:i');

        if ($this->end_date) {
            if ($this->start_date->isSameDay($this->end_date)) {
                $endTime = $this->end_date->format('H:i');
                return "{$startTime} - {$endTime}";
            } else {
                $endTime = $this->end_date->format('d M Y H:i');
                return "{$startTime} - {$endTime}";
            }
        }

        return $startTime;
    }

    /**
     * Get type label in Indonesian
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'meeting' => 'Rapat',
            'deadline' => 'Deadline',
            'holiday' => 'Libur',
            'training' => 'Training',
            'other' => 'Lainnya',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Terjadwal',
            'ongoing' => 'Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'info',
            'ongoing' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', Carbon::now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_date', 'asc');
    }

    /**
     * Scope for today's events
     */
    public function scopeToday($query)
    {
        $today = Carbon::today();
        return $query->whereDate('start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereDate('end_date', '>=', $today)
                    ->orWhereNull('end_date');
            });
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }

    /**
     * Scope for specific month
     */
    public function scopeForMonth($query, $year, $month)
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        return $query->dateRange($startOfMonth, $endOfMonth);
    }
}
