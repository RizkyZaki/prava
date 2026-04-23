<?php

namespace App\Http\Controllers\Api\V1\WorkSchedule;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Salary;
use App\Models\WorkSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkScheduleController extends BaseApiController
{
    /**
     * Work schedule list.
     * Endpoint: GET /api/v1/work-schedule
     */
    public function index(Request $request): JsonResponse
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

        $schedule = $salary?->workSchedule ?? WorkSchedule::getDefault();

        return $this->success([
            'schedule' => $schedule,
        ]);
    }

    /**
     * Today's work schedule.
     * Endpoint: GET /api/v1/work-schedule/today
     */
    public function today(Request $request): JsonResponse
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

        $schedule = $salary?->workSchedule ?? WorkSchedule::getDefault();

        return $this->success([
            'date' => now()->toDateString(),
            'items' => $schedule ? [[
                'name' => $schedule->name,
                'start_time' => optional($schedule->start_time)?->format('H:i:s'),
                'end_time' => optional($schedule->end_time)?->format('H:i:s'),
                'break_start' => optional($schedule->break_start)?->format('H:i:s'),
                'break_end' => optional($schedule->break_end)?->format('H:i:s'),
                'daily_work_hours' => (float) $schedule->daily_work_hours,
                'late_tolerance_minutes' => $schedule->late_tolerance_minutes,
            ]] : [],
        ]);
    }
}
