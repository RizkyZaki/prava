<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceApiController extends Controller
{
    /**
     * Record attendance from fingerprint/face recognition device
     *
     * POST /api/attendance/record
     *
     * Body:
     * {
     *   "fingerprint_id": 6120004,
     *   "datetime": "2025-12-12 08:00:00",
     *   "type": "check_in" | "check_out"
     * }
     */
    public function record(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fingerprint_id' => 'required|integer|exists:users,fingerprint_id',
            'datetime' => 'required|date',
            'type' => 'required|in:check_in,check_out',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by fingerprint_id
        $user = User::where('fingerprint_id', $request->fingerprint_id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User dengan fingerprint_id tersebut tidak ditemukan'
            ], 404);
        }

        $datetime = Carbon::parse($request->datetime);
        $date = $datetime->toDateString();
        $type = $request->type;

        // Find or create attendance record for today
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'attendance_date' => $date,
            ],
            [
                'fingerprint_id' => $request->fingerprint_id,
            ]
        );

        // Update check-in or check-out time
        if ($type === 'check_in') {
            if ($attendance->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Check-in sudah tercatat untuk hari ini',
                    'data' => $attendance
                ], 400);
            }

            $attendance->check_in = $datetime;
            $attendance->fingerprint_id = $request->fingerprint_id ?? $attendance->fingerprint_id;
        } else {
            if (!$attendance->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum melakukan check-in',
                    'data' => null
                ], 400);
            }

            if ($attendance->check_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Check-out sudah tercatat untuk hari ini',
                    'data' => $attendance
                ], 400);
            }

            $attendance->check_out = $datetime;
        }

        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => $type === 'check_in'
                ? 'Check-in berhasil dicatat'
                : 'Check-out berhasil dicatat',
            'data' => [
                'id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'user_name' => $user->name,
                'date' => $attendance->attendance_date->format('Y-m-d'),
                'check_in' => $attendance->check_in?->format('Y-m-d H:i:s'),
                'check_out' => $attendance->check_out?->format('Y-m-d H:i:s'),
                'status' => $attendance->status_label,
                'is_late' => $attendance->isLate(),
                'late_duration' => $attendance->late_duration,
                'work_duration' => $attendance->work_duration,
            ]
        ], 200);
    }

    /**
     * Get user's attendance for today
     *
     * GET /api/attendance/today/{user_id}
     */
    public function today($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => true,
                'message' => 'Belum ada absensi hari ini',
                'data' => null
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data absensi ditemukan',
            'data' => [
                'id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'user_name' => $user->name,
                'date' => $attendance->attendance_date->format('Y-m-d'),
                'check_in' => $attendance->check_in?->format('Y-m-d H:i:s'),
                'check_out' => $attendance->check_out?->format('Y-m-d H:i:s'),
                'status' => $attendance->status_label,
                'is_late' => $attendance->isLate(),
                'late_duration' => $attendance->late_duration,
                'work_duration' => $attendance->work_duration,
            ]
        ], 200);
    }

    /**
     * Get user list (untuk mapping di device)
     *
     * GET /api/users
     */
    public function users()
    {
        $users = User::select('id', 'name', 'email')->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ], 200);
    }
}
