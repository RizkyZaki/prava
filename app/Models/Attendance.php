<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

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
            if ($attendance->check_in && empty($attendance->status)) {
                $checkInTime = Carbon::parse($attendance->check_in);
                $scheduledStart = $checkInTime->copy()->setTime(self::WORK_START_HOUR, 0, 0);
                $lateThreshold = $scheduledStart->copy()->addMinutes(self::LATE_THRESHOLD_MINUTES);

                if ($checkInTime->greaterThan($lateThreshold)) {
                    $attendance->status = 'late';
                } else {
                    $attendance->status = 'present';
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if user is late
     */
    public function isLate(): bool
    {
        if (!$this->check_in) {
            return false;
        }

        $checkInTime = Carbon::parse($this->check_in);
        $scheduledStart = $checkInTime->copy()->setTime(self::WORK_START_HOUR, 0, 0);
        $lateThreshold = $scheduledStart->copy()->addMinutes(self::LATE_THRESHOLD_MINUTES);

        return $checkInTime->greaterThan($lateThreshold);
    }

    /**
     * Get late duration in minutes
     */
    public function getLateDurationAttribute(): ?int
    {
        if (!$this->isLate()) {
            return null;
        }

        $checkInTime = Carbon::parse($this->check_in);
        $scheduledStart = $checkInTime->copy()->setTime(self::WORK_START_HOUR, 0, 0);

        return $checkInTime->diffInMinutes($scheduledStart);
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
