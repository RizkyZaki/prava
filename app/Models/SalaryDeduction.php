<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryDeduction extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_id',
        'salary_id',
        'deduction_date',
        'deduction_type',
        'deduction_amount',
        'minutes_late',
        'minutes_early',
        'hours_short',
        'reason',
        'calculation_details',
        'is_approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'deduction_date' => 'date',
        'deduction_amount' => 'decimal:2',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'calculation_details' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
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
     * Get formatted deduction type
     */
    public function getDeductionTypeLabelAttribute(): string
    {
        return match($this->deduction_type) {
            'late' => 'Terlambat',
            'early_leave' => 'Pulang Cepat',
            'absent' => 'Tidak Masuk',
            'no_check_in' => 'Tidak Check In',
            'no_check_out' => 'Tidak Check Out',
            'short_hours' => 'Jam Kerja Kurang',
            'manual' => 'Manual',
            default => $this->deduction_type,
        };
    }
}
