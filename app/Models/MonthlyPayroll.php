<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MonthlyPayroll extends Model
{
    protected $fillable = [
        'user_id',
        'salary_id',
        'year',
        'month',
        'base_salary',
        'transport_allowance',
        'meal_allowance',
        'position_allowance',
        'other_allowance',
        'total_allowances',
        'gross_salary',
        'total_days_present',
        'total_days_late',
        'total_days_absent',
        'total_work_minutes',
        'total_deductions',
        'late_deductions',
        'early_leave_deductions',
        'absent_deductions',
        'other_deductions',
        'net_salary',
        'status',
        'payment_date',
        'approved_by',
        'approved_at',
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
        'total_deductions' => 'decimal:2',
        'late_deductions' => 'decimal:2',
        'early_leave_deductions' => 'decimal:2',
        'absent_deductions' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salary(): BelongsTo
    {
        return $this->belongsTo(Salary::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get period label (e.g., "January 2026")
     */
    public function getPeriodLabelAttribute(): string
    {
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $monthNames[$this->month] . ' ' . $this->year;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'calculated' => 'Calculated',
            'approved' => 'Approved',
            'paid' => 'Paid',
            default => ucfirst($this->status),
        };
    }
}
