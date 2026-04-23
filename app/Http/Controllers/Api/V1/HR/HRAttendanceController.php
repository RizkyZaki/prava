<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HRAttendanceController extends BaseApiController
{
    /**
     * Attendance list for HR.
     * Endpoint: GET /api/v1/hr/attendance
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::query()->with('user:id,name,email');

        if ($request->filled('date')) {
            $query->whereDate('attendance_date', (string) $request->string('date'));
        } else {
            $query->whereMonth('attendance_date', now()->month)
                ->whereYear('attendance_date', now()->year);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        $items = $query->orderByDesc('attendance_date')->paginate((int) $request->integer('per_page', 20));

        return $this->paginated($items);
    }

    /**
     * Attendance recap for HR.
     * Endpoint: GET /api/v1/hr/attendance/recap
     */
    public function recap(Request $request): JsonResponse
    {
        $year = (int) $request->integer('year', now()->year);
        $month = (int) $request->integer('month', now()->month);

        $query = Attendance::query()
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month);

        return $this->success([
            'period' => ['year' => $year, 'month' => $month],
            'total_records' => (clone $query)->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'late' => (clone $query)->where('status', 'late')->count(),
            'absent' => (clone $query)->where('status', 'absent')->count(),
            'leave' => (clone $query)->where('status', 'leave')->count(),
            'holiday' => (clone $query)->where('status', 'holiday')->count(),
            'total_work_minutes' => (int) (clone $query)->sum('work_duration'),
        ]);
    }
}
