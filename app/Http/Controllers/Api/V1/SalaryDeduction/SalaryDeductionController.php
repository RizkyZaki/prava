<?php

namespace App\Http\Controllers\Api\V1\SalaryDeduction;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\SalaryDeduction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryDeductionController extends BaseApiController
{
    /**
     * Salary deduction list.
     * Endpoint: GET /api/v1/salary-deductions
     */
    public function index(Request $request): JsonResponse
    {
        $deductions = SalaryDeduction::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('deduction_date')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->paginated($deductions);
    }

    /**
     * Salary deductions for current month.
     * Endpoint: GET /api/v1/salary-deductions/current-month
     */
    public function currentMonth(Request $request): JsonResponse
    {
        $query = SalaryDeduction::query()
            ->where('user_id', $request->user()->id)
            ->whereYear('deduction_date', now()->year)
            ->whereMonth('deduction_date', now()->month)
            ->orderByDesc('deduction_date');

        $items = $query->get();

        return $this->success([
            'month' => now()->month,
            'year' => now()->year,
            'total_items' => $items->count(),
            'total_amount' => (float) $items->sum('deduction_amount'),
            'items' => $items,
        ]);
    }
}
