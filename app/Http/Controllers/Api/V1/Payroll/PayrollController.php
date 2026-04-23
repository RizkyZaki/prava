<?php

namespace App\Http\Controllers\Api\V1\Payroll;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\MonthlyPayroll;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends BaseApiController
{
    /**
     * Monthly payroll history.
     * Endpoint: GET /api/v1/payroll
     */
    public function index(Request $request): JsonResponse
    {
        $payrolls = MonthlyPayroll::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate((int) $request->integer('per_page', 12));

        return $this->paginated($payrolls);
    }

    /**
     * Payroll by month.
     * Endpoint: GET /api/v1/payroll/by-month
     */
    public function byMonth(Request $request): JsonResponse
    {
        $year = (int) $request->integer('year', now()->year);
        $month = (int) $request->integer('month', now()->month);

        $payroll = MonthlyPayroll::query()
            ->where('user_id', $request->user()->id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$payroll) {
            return $this->notFound('Payroll data not found for selected period');
        }

        return $this->success($payroll);
    }
}
