<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends BaseApiController
{
    /**
     * Personal attendance list.
     * Endpoint: GET /api/v1/attendance
     */
    public function index(Request $request): JsonResponse
    {
        $attendance = Attendance::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('attendance_date')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->paginated($attendance);
    }

    /**
     * Attendance for current month.
     * Endpoint: GET /api/v1/attendance/current-month
     */
    public function currentMonth(Request $request): JsonResponse
    {
        $attendance = Attendance::query()
            ->where('user_id', $request->user()->id)
            ->whereYear('attendance_date', now()->year)
            ->whereMonth('attendance_date', now()->month)
            ->orderByDesc('attendance_date')
            ->get();

        return $this->success([
            'month' => now()->month,
            'year' => now()->year,
            'total_days' => $attendance->count(),
            'items' => $attendance,
        ]);
    }

    /**
     * Personal attendance recap.
     * Endpoint: GET /api/v1/attendance/recap
     */
    public function recap(Request $request): JsonResponse
    {
        $year = (int) $request->integer('year', now()->year);
        $month = (int) $request->integer('month', now()->month);

        $baseQuery = Attendance::query()
            ->where('user_id', $request->user()->id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month);

        return $this->success([
            'period' => ['year' => $year, 'month' => $month],
            'total' => (clone $baseQuery)->count(),
            'present' => (clone $baseQuery)->where('status', 'present')->count(),
            'late' => (clone $baseQuery)->where('status', 'late')->count(),
            'absent' => (clone $baseQuery)->where('status', 'absent')->count(),
            'leave' => (clone $baseQuery)->where('status', 'leave')->count(),
            'holiday' => (clone $baseQuery)->where('status', 'holiday')->count(),
            'total_work_minutes' => (int) (clone $baseQuery)->sum('work_duration'),
        ]);
    }

    /**
     * Attendance by date.
     * Endpoint: GET /api/v1/attendance/{date}
     */
    public function byDate(Request $request, string $date): JsonResponse
    {
        $attendance = Attendance::query()
            ->where('user_id', $request->user()->id)
            ->whereDate('attendance_date', $date)
            ->first();

        return $this->success([
            'date' => $date,
            'item' => $attendance,
        ]);
    }

    /**
     * Attendance calendar.
     * Endpoint: GET /api/v1/attendance-calendar
     */
    public function calendar(Request $request): JsonResponse
    {
        $year = (int) $request->integer('year', now()->year);
        $month = (int) $request->integer('month', now()->month);

        $items = Attendance::query()
            ->where('user_id', $request->user()->id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->orderBy('attendance_date')
            ->get()
            ->map(function (Attendance $attendance) {
                return [
                    'date' => optional($attendance->attendance_date)->toDateString(),
                    'status' => $attendance->status,
                    'check_in' => optional($attendance->check_in)?->toISOString(),
                    'check_out' => optional($attendance->check_out)?->toISOString(),
                    'work_duration' => $attendance->work_duration,
                ];
            });

        return $this->success([
            'year' => $year,
            'month' => $month,
            'items' => $items,
        ]);
    }
}
