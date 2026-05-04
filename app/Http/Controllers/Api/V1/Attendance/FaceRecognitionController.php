<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\FaceData;
use App\Models\PermittedAbsence;
use App\Models\User;
use App\Services\FaceRecognitionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FaceRecognitionController extends Controller
{
    protected FaceRecognitionService $faceService;

    public function __construct(FaceRecognitionService $faceService)
    {
        $this->faceService = $faceService;
    }

    /**
     * Register/Update face user untuk Face Recognition
     *
     * Digunakan untuk mendaftarkan atau memperbarui foto wajah user di sistem.
     * Endpoint ini hanya bisa diakses dari website dashboard, bukan dari Android app.
     *
     * @group Face Recognition
     * @authenticated
     * @bodyParam face_image file required Image file wajah user (JPEG, PNG, GIF, WebP). Max 5MB. Example: (uploaded_file)
     * @response 201 {
     *   "success": true,
     *   "message": "Wajah berhasil didaftarkan",
     *   "data": {
     *     "face_id": 1,
     *     "face_image_url": "http://example.com/storage/faces/1_xyz.jpg",
     *     "registered_at": "2026-01-08 10:30:00"
     *   }
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Gagal mendaftarkan wajah: ..."
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "User tidak terautentikasi"
     * }
     */
    public function registerFace(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'face_image' => 'required|image|mimes:jpeg,png,gif,webp|max:5120', // max 5MB
            ]);

            // Get authenticated user
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi',
                ], 401);
            }

            // Register face
            $faceData = $this->faceService->registerFace($user, $request->file('face_image'));

            return response()->json([
                'success' => true,
                'message' => 'Wajah berhasil didaftarkan',
                'data' => [
                    'face_id' => $faceData->id,
                    'face_image_url' => $faceData->getFaceImageUrl(),
                    'registered_at' => $faceData->registered_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan wajah: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check Status Registered Face User
     *
     * Mengecek apakah user sudah mendaftarkan wajahnya atau belum,
     * serta mendapatkan informasi detail dari registered face.
     *
     * @group Face Recognition
     * @authenticated
     * @response 200 {
     *   "success": true,
     *   "has_face": true,
     *   "data": {
     *     "registered_at": "2026-01-08 10:30:00",
     *     "face_image_url": "http://example.com/storage/faces/1_xyz.jpg"
     *   }
     * }
     * @response 200 {
     *   "success": true,
     *   "has_face": false,
     *   "data": null
     * }
     */
    public function checkFace(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi',
                ], 401);
            }

            $hasFace = $this->faceService->userHasFace($user);
            $faceData = $user->faceData?->active()->first();

            return response()->json([
                'success' => true,
                'has_face' => $hasFace,
                'data' => $hasFace ? [
                    'registered_at' => $faceData->registered_at,
                    'face_image_url' => $faceData->getFaceImageUrl(),
                ] : null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Attendance Checkin dengan Face Recognition
     *
     * Melakukan check-in attendance menggunakan face recognition.
     * User hanya bisa checkin 1x per hari. Memerlukan approved remote permission.
     *
     * Validasi:
     * - User harus punya approved remote permission untuk hari ini
     * - User harus sudah mendaftarkan wajahnya
     * - Wajah harus cocok dengan yang terdaftar
     * - User belum boleh checkin hari ini
     *
     * @group Attendance - Face Recognition
     * @authenticated
     * @bodyParam user_id integer required ID user yang akan checkin. Example: 1
     * @bodyParam face_image file required Foto wajah user untuk recognition. Max 5MB. Example: (uploaded_file)
     * @response 201 {
     *   "success": true,
     *   "message": "Check-in berhasil",
     *   "type": "check_in",
     *   "data": {
     *     "attendance_id": 1,
     *     "user_id": 1,
     *     "check_in": "2026-01-08 10:35:00",
     *     "status": "present"
     *   }
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Anda sudah melakukan check-in untuk hari ini. Gunakan fitur check-out untuk selesai bekerja.",
     *   "code": "ALREADY_CHECKED_IN"
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Anda belum mendapat izin remote untuk hari ini. Hubungi supervisor Anda.",
     *   "code": "NO_REMOTE_PERMISSION"
     * }
     */
    public function checkInFace(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'face_image' => 'required|image|mimes:jpeg,png,gif,webp|max:5120',
            ]);

            $userId = $request->input('user_id');
            $user = User::findOrFail($userId);
            $today = Carbon::now()->toDateString();

            // ========== CHECK 1: Apakah user punya approved remote permission? ==========
            $remotePermission = PermittedAbsence::where('user_id', $userId)
                ->where('absence_type', 'remote')
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();

            if (!$remotePermission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum mendapat izin remote untuk hari ini. Hubungi supervisor Anda.',
                    'code' => 'NO_REMOTE_PERMISSION',
                ], 403);
            }

            // ========== CHECK 2: Apakah user sudah punya registered face? ==========
            if (!$this->faceService->userHasFace($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wajah Anda belum terdaftar. Silakan daftarkan wajah Anda terlebih dahulu di dashboard.',
                    'code' => 'FACE_NOT_REGISTERED',
                ], 400);
            }

            // ========== CHECK 3: Face recognition - bandingkan dengan registered face ==========
            $faceMatched = $this->faceService->recognizeFace($user, $request->file('face_image'));

            if (!$faceMatched) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wajah tidak cocok dengan data terdaftar. Silakan coba lagi.',
                    'code' => 'FACE_MISMATCH',
                ], 400);
            }

            // ========== CHECK 4: Apakah sudah ada attendance checkin untuk hari ini? ==========
            $existingAttendance = Attendance::where('user_id', $userId)
                ->whereDate('attendance_date', $today)
                ->whereNotNull('check_in')
                ->first();

            if ($existingAttendance) {
                // Sudah checkin hari ini, tidak bisa checkin lagi
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check-in untuk hari ini. Gunakan fitur check-out untuk selesai bekerja.',
                    'code' => 'ALREADY_CHECKED_IN',
                    'data' => [
                        'attendance_id' => $existingAttendance->id,
                        'check_in' => $existingAttendance->check_in,
                        'check_out' => $existingAttendance->check_out,
                    ],
                ], 400);
            }

            // ========== Create attendance record (check-in) ==========
            $attendance = Attendance::create([
                'user_id' => $userId,
                'check_in' => Carbon::now(),
                'attendance_date' => $today,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil',
                'type' => 'check_in',
                'data' => [
                    'attendance_id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                    'check_in' => $attendance->check_in,
                    'status' => $attendance->status,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Attendance Checkout dengan Face Recognition
     *
     * Melakukan check-out attendance menggunakan face recognition.
     * Checkout hanya bisa dilakukan antara jam 16:00 - 21:00 (setelah jam kerja berakhir).
     *
     * Validasi:
     * - User harus punya approved remote permission untuk hari ini
     * - User harus sudah mendaftarkan wajahnya
     * - Wajah harus cocok dengan yang terdaftar
     * - Waktu checkout harus antara 16:00 - 21:00
     * - User harus sudah checkin hari ini tapi belum checkout
     *
     * @group Attendance - Face Recognition
     * @authenticated
     * @bodyParam user_id integer required ID user yang akan checkout. Example: 1
     * @bodyParam face_image file required Foto wajah user untuk recognition. Max 5MB. Example: (uploaded_file)
     * @response 200 {
     *   "success": true,
     *   "message": "Check-out berhasil",
     *   "type": "check_out",
     *   "data": {
     *     "attendance_id": 1,
     *     "user_id": 1,
     *     "check_in": "2026-01-08 10:35:00",
     *     "check_out": "2026-01-08 17:30:00",
     *     "work_duration": 475,
     *     "status": "present"
     *   }
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Anda hanya bisa checkout setelah jam kerja berakhir (16:00).",
     *   "code": "CHECKOUT_TOO_EARLY",
     *   "earliest_checkout_time": "16:00"
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Anda belum mendapat izin remote untuk hari ini. Hubungi supervisor Anda.",
     *   "code": "NO_REMOTE_PERMISSION"
     * }
     */
    public function checkOutFace(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'face_image' => 'required|image|mimes:jpeg,png,gif,webp|max:5120',
            ]);

            $userId = $request->input('user_id');
            $user = User::findOrFail($userId);
            $today = Carbon::now()->toDateString();
            $now = Carbon::now();

            // ========== CHECK 1: Apakah user punya approved remote permission? ==========
            $remotePermission = PermittedAbsence::where('user_id', $userId)
                ->where('absence_type', 'remote')
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();

            if (!$remotePermission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum mendapat izin remote untuk hari ini. Hubungi supervisor Anda.',
                    'code' => 'NO_REMOTE_PERMISSION',
                ], 403);
            }

            // ========== CHECK 2: Apakah user sudah punya registered face? ==========
            if (!$this->faceService->userHasFace($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wajah Anda belum terdaftar. Silakan daftarkan wajah Anda terlebih dahulu di dashboard.',
                    'code' => 'FACE_NOT_REGISTERED',
                ], 400);
            }

            // ========== CHECK 3: Face recognition - bandingkan dengan registered face ==========
            $faceMatched = $this->faceService->recognizeFace($user, $request->file('face_image'));

            if (!$faceMatched) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wajah tidak cocok dengan data terdaftar. Silakan coba lagi.',
                    'code' => 'FACE_MISMATCH',
                ], 400);
            }

            // ========== CHECK 4: Validasi time window untuk checkout (16:00 - 21:00) ==========
            $currentHour = $now->hour;
            $workEndHour = 16; // 16:00 (jam kerja berakhir)
            $checkoutMaxHour = 21; // 21:00 (5 jam setelah jam kerja berakhir)

            if ($currentHour < $workEndHour) {
                // Belum mencapai jam kerja berakhir
                $waitMinutes = ($workEndHour - $currentHour) * 60 - $now->minute;
                return response()->json([
                    'success' => false,
                    'message' => "Anda hanya bisa checkout setelah jam kerja berakhir (16:00). Tunggu {$waitMinutes} menit lagi.",
                    'code' => 'CHECKOUT_TOO_EARLY',
                    'earliest_checkout_time' => '16:00',
                ], 400);
            }

            if ($currentHour >= $checkoutMaxHour) {
                // Sudah melewati batas maksimal checkout
                return response()->json([
                    'success' => false,
                    'message' => 'Sudah melewati batas waktu checkout maksimal (21:00). Hubungi supervisor Anda.',
                    'code' => 'CHECKOUT_TOO_LATE',
                    'checkout_deadline' => '21:00',
                ], 400);
            }

            // ========== CHECK 5: Apakah sudah ada attendance checkin untuk hari ini? ==========
            $attendance = Attendance::where('user_id', $userId)
                ->whereDate('attendance_date', $today)
                ->whereNotNull('check_in')
                ->first();

            if (!$attendance) {
                // Belum checkin
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum melakukan check-in untuk hari ini. Silakan check-in terlebih dahulu.',
                    'code' => 'NOT_CHECKED_IN',
                ], 400);
            }

            if ($attendance->check_out) {
                // Sudah checkout
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan check-out untuk hari ini.',
                    'code' => 'ALREADY_CHECKED_OUT',
                    'data' => [
                        'attendance_id' => $attendance->id,
                        'check_in' => $attendance->check_in,
                        'check_out' => $attendance->check_out,
                        'work_duration' => $attendance->work_duration,
                    ],
                ], 400);
            }

            // ========== Update checkout ==========
            $attendance->update([
                'check_out' => $now,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-out berhasil',
                'type' => 'check_out',
                'data' => [
                    'attendance_id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                    'check_in' => $attendance->check_in,
                    'check_out' => $attendance->check_out,
                    'work_duration' => $attendance->work_duration,
                    'status' => $attendance->status,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Delete/Hapus Face Data User (Admin Only)
     *
     * Menghapus data wajah (face data) user dari sistem.
     * Endpoint ini hanya bisa diakses oleh super admin.
     * User akan perlu mendaftarkan wajah baru jika ingin menggunakan fitur face recognition lagi.
     *
     * @group Face Recognition - Admin
     * @authenticated
     * @urlParam userId integer required ID user yang face data-nya akan dihapus. Example: 1
     * @response 200 {
     *   "success": true,
     *   "message": "Face data berhasil dihapus"
     * }
     * @response 403 {
     *   "success": false,
     *   "message": "Anda tidak memiliki akses untuk menghapus face data"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "User tidak ditemukan"
     * }
     */
    public function deleteFace(Request $request, $userId): JsonResponse
    {
        try {
            // Check if authorized (hanya admin)
            if (!auth()->user()->hasRole('super_admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menghapus face data',
                ], 403);
            }

            $user = User::findOrFail($userId);
            $this->faceService->deleteFace($user);

            return response()->json([
                'success' => true,
                'message' => 'Face data berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 400);
        }
    }
}
