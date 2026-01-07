<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance_records';

    protected $fillable = [
        'user_id',
        'fingerprint_id',
        'check_in',
        'check_out',
        'attendance_date',
        'status',
        'notes',
        'work_duration',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'attendance_date' => 'date',
    ];

    // Working hours configuration
    const WORK_START_HOUR = 8; // 08:00
    const WORK_END_HOUR = 16;  // 16:00 (4 PM)
    const LATE_THRESHOLD_MINUTES = 15; // Late if check-in after 08:15
    const HALF_DAY_HOURS = 4; // Minimum hours for half day

    protected static function booted()
    {
        // Auto-calculate status and work duration on save
        static::saving(function ($attendance) {
            // Set attendance_date from check_in if not set
            if (empty($attendance->attendance_date) && $attendance->check_in) {
                $attendance->attendance_date = Carbon::parse($attendance->check_in)->toDateString();
            }

            // Calculate work duration if both check_in and check_out exist
            if ($attendance->check_in && $attendance->check_out) {
                $checkIn = Carbon::parse($attendance->check_in);
                $checkOut = Carbon::parse($attendance->check_out);
                // diffInMinutes second param true = absolute value (always positive)
                $attendance->work_duration = $checkIn->diffInMinutes($checkOut, true);
            }

            // Auto-determine status based on check-in time
            // Only auto-calculate if status is empty OR if status is 'present' but check-in is actually late
            if ($attendance->check_in) {
                $checkInTime = Carbon::parse($attendance->check_in);
                $scheduledStart = $checkInTime->copy()->setTime(self::WORK_START_HOUR, 0, 0);
                $lateThreshold = $scheduledStart->copy()->addMinutes(self::LATE_THRESHOLD_MINUTES);

                // Set status if empty
                if (empty($attendance->status)) {
                    if ($checkInTime->greaterThan($lateThreshold)) {
                        $attendance->status = 'late';
                    } else {
                        $attendance->status = 'present';
                    }
                }
                // Also update if currently 'present' but check-in time indicates late
                elseif ($attendance->status === 'present' && $checkInTime->greaterThan($lateThreshold)) {
                    $attendance->status = 'late';
                }
            }
        });

        // Auto-calculate deduction after save (when attendance is complete)
        static::saved(function ($attendance) {
            // Calculate deduction when attendance has check-in (even without checkout yet)
            if ($attendance->check_in) {
                app(\App\Services\SalaryDeductionService::class)->calculateDeduction($attendance);
            }
        });

        // Also trigger on update
        static::updated(function ($attendance) {
            if ($attendance->check_in) {
                app(\App\Services\SalaryDeductionService::class)->calculateDeduction($attendance);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(SalaryDeduction::class);
    }

    /**
     * Check if user is late
     */
    public function isLate(): bool
    {
        if (!$this->check_in) {
            return false;
        }

        // Get user's active salary with work schedule
        $salary = Salary::where('user_id', $this->user_id)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $this->attendance_date)
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $this->attendance_date);
            })
            ->first();

        // Get work schedule (use default if not configured)
        $workSchedule = $salary?->workSchedule ?? WorkSchedule::getDefault();

        if (!$workSchedule) {
            // Fallback to constants if no schedule
            $checkInTime = Carbon::parse($this->check_in);
            $scheduledStart = $checkInTime->copy()->setTime(self::WORK_START_HOUR, 0, 0);
            $lateThreshold = $scheduledStart->copy()->addMinutes(self::LATE_THRESHOLD_MINUTES);
            return $checkInTime->greaterThan($lateThreshold);
        }

        $checkInTime = Carbon::parse($this->check_in);
        $scheduledStart = $checkInTime->copy()->setTimeFrom(Carbon::parse($workSchedule->start_time));
        $lateThreshold = $scheduledStart->copy()->addMinutes($workSchedule->late_tolerance_minutes);

        return $checkInTime->greaterThan($lateThreshold);
    }

    /**
     * Get late duration in minutes
     */
    public function getLateDurationAttribute(): ?int
    {
        if (!$this->check_in) {
            return null;
        }

        // Get user's active salary with work schedule
        $salary = Salary::where('user_id', $this->user_id)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $this->attendance_date)
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $this->attendance_date);
            })
            ->first();

        // Get work schedule (use default if not configured)
        $workSchedule = $salary?->workSchedule ?? WorkSchedule::getDefault();

        $checkInTime = Carbon::parse($this->check_in);

        if (!$workSchedule) {
            // Fallback to constants
            $scheduledStart = $checkInTime->copy()->setTime(self::WORK_START_HOUR, 0, 0);
        } else {
            $scheduledStart = $checkInTime->copy()->setTimeFrom(Carbon::parse($workSchedule->start_time));
        }

        // Return 0 if not late, otherwise return positive minutes late
        if ($checkInTime->lessThanOrEqualTo($scheduledStart)) {
            return 0;
        }

        // Use absolute value to ensure positive result
        return (int) $checkInTime->diffInMinutes($scheduledStart, true);
    }

    /**
     * Get formatted work duration
     */
    public function getFormattedWorkDurationAttribute(): ?string
    {
        if (!$this->work_duration) {
            return null;
        }

        $hours = floor($this->work_duration / 60);
        $minutes = $this->work_duration % 60;

        return sprintf('%d jam %d menit', $hours, $minutes);
    }

    /**
     * Check if attendance is complete (has both check-in and check-out)
     */
    public function isComplete(): bool
    {
        return !is_null($this->check_in) && !is_null($this->check_out);
    }

    /**
     * Scope for today's attendance
     */
    public function scopeToday($query)
    {
        return $query->whereDate('attendance_date', Carbon::today());
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    /**
     * Scope for current month
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereYear('attendance_date', Carbon::now()->year)
            ->whereMonth('attendance_date', Carbon::now()->month);
    }

    /**
     * Get status color for badge
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present' => 'success',
            'late' => 'warning',
            'half_day' => 'info',
            'absent' => 'danger',
            'leave' => 'gray',
            'holiday' => 'primary',
            default => 'gray',
        };
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'half_day' => 'Setengah Hari',
            'absent' => 'Tidak Hadir',
            'leave' => 'Cuti',
            'holiday' => 'Libur',
            default => ucfirst($this->status),
        };
    }
}
