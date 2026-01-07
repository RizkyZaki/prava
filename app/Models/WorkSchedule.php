<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'late_tolerance_minutes',
        'early_leave_tolerance_minutes',
        'daily_work_hours',
        'hourly_deduction_rate',
        'is_default',
        'is_active',
        'description',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'break_start' => 'datetime:H:i:s',
        'break_end' => 'datetime:H:i:s',
        'daily_work_hours' => 'decimal:2',
        'hourly_deduction_rate' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get salaries using this schedule
     */
    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    /**
     * Calculate break duration in minutes
     */
    public function getBreakDurationAttribute(): int
    {
        if (!$this->break_start || !$this->break_end) {
            return 0;
        }

        return $this->break_start->diffInMinutes($this->break_end);
    }

    /**
     * Calculate expected work minutes (excluding break)
     */
    public function getExpectedWorkMinutesAttribute(): int
    {
        $totalMinutes = $this->start_time->diffInMinutes($this->end_time);
        return $totalMinutes - $this->break_duration;
    }

    /**
     * Get the default work schedule
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)
            ->where('is_active', true)
            ->first();
    }
}
