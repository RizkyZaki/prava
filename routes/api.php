<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| API untuk integrasi dengan device fingerprint/face recognition
|
*/

// Attendance API untuk device fingerprint
Route::prefix('attendance')->group(function () {
    // Record attendance (check-in/check-out)
    Route::post('/record', [AttendanceApiController::class, 'record']);

    // Get today's attendance for specific user
    Route::get('/today/{user_id}', [AttendanceApiController::class, 'today']);
});

// Get user list untuk mapping di device
Route::get('/users', [AttendanceApiController::class, 'users']);
