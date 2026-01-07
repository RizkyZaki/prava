<?php

namespace App\Services;

use App\Models\MonthlyPayroll;
use App\Models\Salary;
use App\Models\SalaryDeduction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyPayrollService
{
    /**
     * Generate payroll for a specific user and month
     */
    public function generatePayroll(int $userId, int $year, int $month): ?MonthlyPayroll
    {
        // Get active salary for the period
        $salary = Salary::where('user_id', $userId)
            ->where('is_active', true)
            ->where('effective_from', '<=', Carbon::create($year, $month, 1))
            ->where(function ($query) use ($year, $month) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', Carbon::create($year, $month, 1)->endOfMonth());
            })
            ->first();

        if (!$salary) {
            return null; // No active salary for this period
        }

        // Get attendance summary for the month
        $attendanceSummary = $this->getAttendanceSummary($userId, $year, $month);

        // Get deductions for the month
        $deductionsSummary = $this->getDeductionsSummary($userId, $year, $month);

        // Calculate net salary
        $netSalary = $salary->gross_salary - $deductionsSummary['total'];

        // Create or update payroll record
        return MonthlyPayroll::updateOrCreate(
            [
                'user_id' => $userId,
                'year' => $year,
                'month' => $month,
            ],
            [
                'salary_id' => $salary->id,
                'base_salary' => $salary->base_salary,
                'transport_allowance' => $salary->transport_allowance,
                'meal_allowance' => $salary->meal_allowance,
                'position_allowance' => $salary->position_allowance,
                'other_allowance' => $salary->other_allowance,
                'total_allowances' => $salary->total_allowances,
                'gross_salary' => $salary->gross_salary,
                'total_days_present' => $attendanceSummary['days_present'],
                'total_days_late' => $attendanceSummary['days_late'],
                'total_days_absent' => $attendanceSummary['days_absent'],
                'total_work_minutes' => $attendanceSummary['total_work_minutes'],
                'total_deductions' => $deductionsSummary['total'],
                'late_deductions' => $deductionsSummary['late'],
                'early_leave_deductions' => $deductionsSummary['early_leave'],
                'absent_deductions' => $deductionsSummary['absent'],
                'other_deductions' => $deductionsSummary['other'],
                'net_salary' => $netSalary,
                'status' => 'calculated',
            ]
        );
    }

    /**
     * Generate payroll for all users in a specific month
     */
    public function generateBulkPayroll(int $year, int $month): array
    {
        $users = User::whereHas('salaries', function ($query) use ($year, $month) {
            $query->where('is_active', true)
                ->where('effective_from', '<=', Carbon::create($year, $month, 1))
                ->where(function ($q) use ($year, $month) {
                    $q->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', Carbon::create($year, $month, 1)->endOfMonth());
                });
        })->get();

        $results = [
            'success' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($users as $user) {
            try {
                $payroll = $this->generatePayroll($user->id, $year, $month);
                if ($payroll) {
                    $results['success']++;
                    $results['details'][] = [
                        'user' => $user->name,
                        'status' => 'success',
                        'payroll_id' => $payroll->id,
                    ];
                } else {
                    $results['failed']++;
                    $results['details'][] = [
                        'user' => $user->name,
                        'status' => 'failed',
                        'reason' => 'No active salary',
                    ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'user' => $user->name,
                    'status' => 'error',
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get attendance summary for a user in a specific month
     */
    protected function getAttendanceSummary(int $userId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Query attendance_records (renamed from attendances)
        $attendances = DB::table('attendance_records')
            ->where('user_id', $userId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        return [
            'days_present' => $attendances->whereIn('status', ['present', 'late'])->count(),
            'days_late' => $attendances->where('status', 'late')->count(),
            'days_absent' => $attendances->where('status', 'absent')->count(),
            'total_work_minutes' => $attendances->sum('work_duration'),
        ];
    }

    /**
     * Get deductions summary for a user in a specific month
     */
    protected function getDeductionsSummary(int $userId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $deductions = SalaryDeduction::where('user_id', $userId)
            ->whereBetween('deduction_date', [$startDate, $endDate])
            ->where('is_approved', true) // Only count approved deductions
            ->get();

        return [
            'total' => $deductions->sum('deduction_amount'),
            'late' => $deductions->where('deduction_type', 'late')->sum('deduction_amount'),
            'early_leave' => $deductions->where('deduction_type', 'early_leave')->sum('deduction_amount'),
            'absent' => $deductions->whereIn('deduction_type', ['absent', 'no_check_in', 'no_check_out'])->sum('deduction_amount'),
            'other' => $deductions->where('deduction_type', 'short_hours')->sum('deduction_amount'),
        ];
    }

    /**
     * Approve payroll
     */
    public function approvePayroll(MonthlyPayroll $payroll, int $approvedBy): bool
    {
        return $payroll->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    /**
     * Mark payroll as paid
     */
    public function markAsPaid(MonthlyPayroll $payroll, ?Carbon $paymentDate = null): bool
    {
        return $payroll->update([
            'status' => 'paid',
            'payment_date' => $paymentDate ?? now(),
        ]);
    }
}
