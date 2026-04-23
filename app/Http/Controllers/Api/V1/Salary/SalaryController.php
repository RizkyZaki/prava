<?php

namespace App\Http\Controllers\Api\V1\Salary;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Salary;
use App\Models\SalaryDeduction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryController extends BaseApiController
{
    /**
     * User salary list.
     * Endpoint: GET /api/v1/salary
     */
    public function index(Request $request): JsonResponse
    {
        $salaries = Salary::query()
            ->where('user_id', $request->user()->id)
            ->with('workSchedule')
            ->orderByDesc('effective_from')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->paginated($salaries);
    }

    /**
     * Active salary details.
     * Endpoint: GET /api/v1/salary/details
     */
    public function detail(Request $request): JsonResponse
    {
        $salary = Salary::query()
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', today())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', today());
            })
            ->with('workSchedule')
            ->first();

        if (!$salary) {
            return $this->notFound('No active salary found');
        }

        return $this->success($salary);
    }

    /**
     * Salary component breakdown.
     * Endpoint: GET /api/v1/salary/breakdown
     */
    public function breakdown(Request $request): JsonResponse
    {
        $salary = Salary::query()
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', today())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', today());
            })
            ->first();

        if (!$salary) {
            return $this->notFound('No active salary found');
        }

        return $this->success([
            'base_salary' => (float) $salary->base_salary,
            'allowances' => [
                'transport' => (float) $salary->transport_allowance,
                'meal' => (float) $salary->meal_allowance,
                'position' => (float) $salary->position_allowance,
                'other' => (float) $salary->other_allowance,
                'total' => (float) $salary->total_allowances,
            ],
            'gross_salary' => (float) $salary->gross_salary,
            'daily_salary' => (float) $salary->daily_salary,
            'hourly_salary' => (float) $salary->hourly_salary,
        ]);
    }

    /**
     * Salary deduction list.
     * Endpoint: GET /api/v1/salary/deductions
     */
    public function deductions(Request $request): JsonResponse
    {
        $deductions = SalaryDeduction::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('deduction_date')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->paginated($deductions);
    }

    /**
     * Salary estimation.
     * Endpoint: GET /api/v1/salary/estimate
     */
    public function estimate(Request $request): JsonResponse
    {
        $salary = Salary::query()
            ->where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', today())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', today());
            })
            ->first();

        if (!$salary) {
            return $this->notFound('No active salary found');
        }

        $year = (int) $request->integer('year', now()->year);
        $month = (int) $request->integer('month', now()->month);

        $totalDeductions = (float) SalaryDeduction::query()
            ->where('user_id', $request->user()->id)
            ->whereYear('deduction_date', $year)
            ->whereMonth('deduction_date', $month)
            ->sum('deduction_amount');

        $grossSalary = (float) $salary->gross_salary;

        return $this->success([
            'period' => [
                'year' => $year,
                'month' => $month,
            ],
            'gross_salary' => $grossSalary,
            'estimated_deductions' => $totalDeductions,
            'estimated_net_salary' => max(0, $grossSalary - $totalDeductions),
        ]);
    }
}
