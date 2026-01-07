<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\PermittedAbsence;
use App\Models\Salary;
use App\Models\SalaryDeduction;
use App\Models\WorkSchedule;
use Carbon\Carbon;

class SalaryDeductionService
{
    /**
     * Calculate and create deduction for an attendance record
     */
    public function calculateDeduction(Attendance $attendance): ?SalaryDeduction
    {
        // Check if user has approved permitted absence for this date
        if ($this->hasApprovedPermittedAbsence($attendance->user_id, $attendance->attendance_date)) {
            return null; // No deduction if there's approved absence
        }

        // Get active salary for user
        $salary = Salary::where('user_id', $attendance->user_id)
            ->where('is_active', true)
            ->where('effective_from', '<=', $attendance->attendance_date)
            ->where(function ($query) use ($attendance) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $attendance->attendance_date);
            })
            ->first();

        if (!$salary) {
            return null; // No active salary configuration
        }

        // Check if date is holiday
        if ($this->isHoliday($attendance->attendance_date)) {
            return null; // No deduction on holidays
        }

        // Get work schedule
        $workSchedule = $salary->workSchedule ?? WorkSchedule::getDefault();
        if (!$workSchedule) {
            return null; // No work schedule configured
        }

        // Calculate deduction amount
        $deductionData = $this->calculateDeductionAmount($attendance, $salary, $workSchedule);

        if ($deductionData['amount'] <= 0) {
            return null; // No deduction needed
        }

        // Create or update deduction record
        return SalaryDeduction::updateOrCreate(
            [
                'user_id' => $attendance->user_id,
                'attendance_id' => $attendance->id,
                'deduction_date' => $attendance->attendance_date,
            ],
            [
                'salary_id' => $salary->id,
                'deduction_type' => $deductionData['type'],
                'deduction_amount' => $deductionData['amount'],
                'minutes_late' => $deductionData['minutes_late'] ?? 0,
                'minutes_early' => $deductionData['minutes_early'] ?? 0,
                'hours_short' => $deductionData['hours_short'] ?? 0,
                'reason' => $deductionData['reason'],
                'calculation_details' => $deductionData['calculation_details'],
            ]
        );
    }

    /**
     * Check if user has approved permitted absence for a specific date
     */
    protected function hasApprovedPermittedAbsence(int $userId, Carbon $date): bool
    {
        return PermittedAbsence::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    /**
     * Calculate deduction amount based on attendance
     */
    protected function calculateDeductionAmount(Attendance $attendance, Salary $salary, WorkSchedule $workSchedule): array
    {
        $deductionAmount = 0;
        $minutesLate = 0;
        $minutesEarly = 0;
        $hoursShort = 0;
        $deductionType = null;
        $reason = null;
        $calculationDetails = [];

        $dailySalary = $salary->daily_salary;
        $hourlySalary = $salary->hourly_salary;

        // No check-in
        if (!$attendance->check_in) {
            if ($salary->enable_absent_deduction) {
                $deductionAmount = $dailySalary; // Full day deduction
                $deductionType = 'no_check_in';
                $reason = 'Tidak melakukan check-in';
                $calculationDetails = [
                    'daily_salary' => $dailySalary,
                    'deduction_rate' => 100,
                ];
            }
            return [
                'amount' => $deductionAmount,
                'type' => $deductionType,
                'reason' => $reason,
                'calculation_details' => $calculationDetails,
            ];
        }

        // Check for late arrival
        if ($salary->enable_late_deduction && $attendance->isLate()) {
            $minutesLate = $attendance->late_duration;
            $toleranceMinutes = $workSchedule->late_tolerance_minutes;

            if ($minutesLate > $toleranceMinutes) {
                $deductibleMinutes = $minutesLate - $toleranceMinutes;
                $deductionAmount += ($hourlySalary / 60) * $deductibleMinutes;
                $deductionType = 'late';
                $reason = sprintf('Terlambat %d menit (toleransi %d menit)', $minutesLate, $toleranceMinutes);
                $calculationDetails['late'] = [
                    'minutes_late' => $minutesLate,
                    'tolerance_minutes' => $toleranceMinutes,
                    'deductible_minutes' => $deductibleMinutes,
                    'hourly_salary' => $hourlySalary,
                    'deduction_amount' => $deductionAmount,
                ];
            }
        }

        // Check for early leave
        if ($salary->enable_early_leave_deduction && $attendance->check_out) {
            $checkOutTime = Carbon::parse($attendance->check_out);
            $scheduledEnd = $checkOutTime->copy()->setTimeFrom($workSchedule->end_time);

            if ($checkOutTime->lessThan($scheduledEnd)) {
                $minutesEarly = $scheduledEnd->diffInMinutes($checkOutTime);
                $toleranceMinutes = $workSchedule->early_leave_tolerance_minutes;

                if ($minutesEarly > $toleranceMinutes) {
                    $deductibleMinutes = $minutesEarly - $toleranceMinutes;
                    $earlyLeaveDeduction = ($hourlySalary / 60) * $deductibleMinutes;
                    $deductionAmount += $earlyLeaveDeduction;
                    $deductionType = $deductionType ?? 'early_leave';
                    $reason = $reason ? $reason . ', Pulang cepat ' . $minutesEarly . ' menit' : 'Pulang cepat ' . $minutesEarly . ' menit';
                    $calculationDetails['early_leave'] = [
                        'minutes_early' => $minutesEarly,
                        'tolerance_minutes' => $toleranceMinutes,
                        'deductible_minutes' => $deductibleMinutes,
                        'hourly_salary' => $hourlySalary,
                        'deduction_amount' => $earlyLeaveDeduction,
                    ];
                }
            }
        }

        // Check for insufficient work hours
        if ($attendance->work_duration) {
            $expectedMinutes = $workSchedule->expected_work_minutes;
            $actualMinutes = $attendance->work_duration;

            if ($actualMinutes < $expectedMinutes) {
                $shortMinutes = $expectedMinutes - $actualMinutes;
                $shortHours = $shortMinutes / 60;
                $shortHoursDeduction = $shortHours * $hourlySalary;
                $deductionAmount += $shortHoursDeduction;
                $hoursShort = $shortHours;
                $deductionType = $deductionType ?? 'short_hours';
                $reason = $reason ? $reason . sprintf(', Jam kerja kurang %.2f jam', $shortHours) : sprintf('Jam kerja kurang %.2f jam', $shortHours);
                $calculationDetails['short_hours'] = [
                    'expected_minutes' => $expectedMinutes,
                    'actual_minutes' => $actualMinutes,
                    'short_minutes' => $shortMinutes,
                    'short_hours' => $shortHours,
                    'hourly_salary' => $hourlySalary,
                    'deduction_amount' => $shortHoursDeduction,
                ];
            }
        }

        return [
            'amount' => round($deductionAmount, 2),
            'type' => $deductionType,
            'reason' => $reason,
            'minutes_late' => $minutesLate,
            'minutes_early' => $minutesEarly,
            'hours_short' => $hoursShort,
            'calculation_details' => $calculationDetails,
        ];
    }

    /**
     * Check if date is a holiday based on Event calendar
     */
    protected function isHoliday(Carbon $date): bool
    {
        // For now, return false - holiday checking can be implemented later
        return false;

        // Original implementation (uncomment when event_type column is added):
        // return Event::where('event_type', 'holiday')
        //     ->where('start_date', '<=', $date)
        //     ->where('end_date', '>=', $date)
        //     ->exists();
    }

    /**
     * Calculate total deductions for a user in a date range
     */
    public function calculateMonthlyDeductions(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $deductions = SalaryDeduction::where('user_id', $userId)
            ->whereBetween('deduction_date', [$startDate, $endDate])
            ->get();

        return [
            'total_deductions' => $deductions->sum('deduction_amount'),
            'late_count' => $deductions->where('deduction_type', 'late')->count(),
            'early_leave_count' => $deductions->where('deduction_type', 'early_leave')->count(),
            'absent_count' => $deductions->where('deduction_type', 'absent')->count(),
            'deductions' => $deductions,
        ];
    }

    /**
     * Recalculate deductions for all attendance in a date range
     */
    public function recalculateDeductions(int $userId, Carbon $startDate, Carbon $endDate): int
    {
        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        $count = 0;
        foreach ($attendances as $attendance) {
            if ($this->calculateDeduction($attendance)) {
                $count++;
            }
        }

        return $count;
    }
}
