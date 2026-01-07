<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Salary extends Model
{
    protected $fillable = [
        'user_id',
        'work_schedule_id',
        'base_salary',
        'transport_allowance',
        'meal_allowance',
        'position_allowance',
        'other_allowance',
        'total_allowances',
        'gross_salary',
        'enable_late_deduction',
        'enable_early_leave_deduction',
        'enable_absent_deduction',
        'effective_from',
        'effective_to',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'meal_allowance' => 'decimal:2',
        'position_allowance' => 'decimal:2',
        'other_allowance' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'enable_late_deduction' => 'boolean',
        'enable_early_leave_deduction' => 'boolean',
        'enable_absent_deduction' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function (Salary $salary) {
            // Auto-calculate total allowances
            $salary->total_allowances =
                $salary->transport_allowance +
                $salary->meal_allowance +
                $salary->position_allowance +
                $salary->other_allowance;

            // Auto-calculate gross salary
            $salary->gross_salary = $salary->base_salary + $salary->total_allowances;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(SalaryDeduction::class);
    }

    /**
     * Get daily salary amount
     */
    public function getDailySalaryAttribute(): float
    {
        // Assume 30 working days per month
        return $this->gross_salary / 30;
    }

    /**
     * Get hourly salary amount
     */
    public function getHourlySalaryAttribute(): float
    {
        $workHours = $this->workSchedule?->daily_work_hours ?? 8;
        return $this->daily_salary / $workHours;
    }

    /**
     * Check if salary is currently active
     */
    public function isCurrentlyActive(): bool
    {
        $now = Carbon::now();

        return $this->is_active &&
               $this->effective_from <= $now &&
               ($this->effective_to === null || $this->effective_to >= $now);
    }
}
