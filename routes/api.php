<?php

use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\V1\Attendance\FaceRecognitionController;
use App\Http\Controllers\Api\V1\Account\AccountController;
use App\Http\Controllers\Api\V1\Activity\ActivityController;
use App\Http\Controllers\Api\V1\Attendance\AttendanceController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Dashboard\DashboardController;
use App\Http\Controllers\Api\V1\HR\EmployeeController;
use App\Http\Controllers\Api\V1\HR\HRAttendanceController;
use App\Http\Controllers\Api\V1\Notification\NotificationController;
use App\Http\Controllers\Api\V1\Payroll\PayrollController;
use App\Http\Controllers\Api\V1\Permission\PermissionController;
use App\Http\Controllers\Api\V1\Project\ProjectController;
use App\Http\Controllers\Api\V1\Salary\SalaryController;
use App\Http\Controllers\Api\V1\SalaryDeduction\SalaryDeductionController;
use App\Http\Controllers\Api\V1\System\SystemController;
use App\Http\Controllers\Api\V1\Ticket\TicketController;
use App\Http\Controllers\Api\V1\WorkSchedule\WorkScheduleController;
use App\Http\Controllers\Api\WhatsappWebhookController;
use Illuminate\Support\Facades\Route;

$apiVersion = config('api.version', 'v1');
$authThrottle = 'throttle:' . config('api.throttle.auth', '10,1');
$defaultThrottle = 'throttle:' . config('api.throttle.default', '60,1');

Route::prefix($apiVersion)->group(function () use ($authThrottle, $defaultThrottle) {
    Route::get('/server-status', [SystemController::class, 'status']);
    Route::get('/version', [SystemController::class, 'version']);
    Route::get('/config', [SystemController::class, 'config']);

    Route::prefix('auth')->middleware($authThrottle)->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth:sanctum', $defaultThrottle])->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/me', [AuthController::class, 'me']);
        });

        Route::prefix('dashboard')->group(function () {
            Route::get('/', [DashboardController::class, 'index']);
            Route::get('/greeting', [DashboardController::class, 'greeting']);
            Route::get('/time', [DashboardController::class, 'time']);
            Route::get('/statistics', [DashboardController::class, 'statistics']);
        });

        Route::prefix('salary')->group(function () {
            Route::get('/', [SalaryController::class, 'index']);
            Route::get('/details', [SalaryController::class, 'detail']);
            Route::get('/breakdown', [SalaryController::class, 'breakdown']);
            Route::get('/deductions', [SalaryController::class, 'deductions']);
            Route::get('/estimate', [SalaryController::class, 'estimate']);
        });

        Route::prefix('attendance')->group(function () {
            Route::get('/', [AttendanceController::class, 'index']);
            Route::get('/current-month', [AttendanceController::class, 'currentMonth']);
            Route::get('/recap', [AttendanceController::class, 'recap']);
            Route::get('/{date}', [AttendanceController::class, 'byDate'])
                ->where('date', '\\d{4}-\\d{2}-\\d{2}');
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);
        });

        Route::prefix('work-schedule')->group(function () {
            Route::get('/', [WorkScheduleController::class, 'index']);
            Route::get('/today', [WorkScheduleController::class, 'today']);
        });

        Route::prefix('salary-deductions')->group(function () {
            Route::get('/', [SalaryDeductionController::class, 'index']);
            Route::get('/current-month', [SalaryDeductionController::class, 'currentMonth']);
        });

        Route::prefix('payroll')->group(function () {
            Route::get('/', [PayrollController::class, 'index']);
            Route::get('/by-month', [PayrollController::class, 'byMonth']);
        });

        Route::prefix('account')->group(function () {
            Route::get('/', [AccountController::class, 'index']);
            Route::get('/profile', [AccountController::class, 'profile']);
            Route::put('/profile', [AccountController::class, 'updateProfile']);
        });

        Route::prefix('employees')->group(function () {
            Route::get('/', [EmployeeController::class, 'index']);
            Route::get('/{id}', [EmployeeController::class, 'show']);
        });

        Route::prefix('hr/attendance')->group(function () {
            Route::get('/', [HRAttendanceController::class, 'index']);
            Route::get('/recap', [HRAttendanceController::class, 'recap']);
        });

        Route::get('/attendance-calendar', [AttendanceController::class, 'calendar']);

        Route::prefix('activities')->group(function () {
            Route::get('/', [ActivityController::class, 'index']);
            Route::get('/{id}', [ActivityController::class, 'show']);
        });
        Route::get('/activity-calendar', [ActivityController::class, 'calendar']);

        Route::prefix('project')->group(function () {
            Route::get('/', [ProjectController::class, 'index']);
            Route::get('/timeline', [ProjectController::class, 'timeline']);
            Route::get('/board', [ProjectController::class, 'board']);
            Route::get('/{id}', [ProjectController::class, 'show']);
        });

        Route::prefix('ticket')->group(function () {
            Route::get('/', [TicketController::class, 'index']);
            Route::post('/', [TicketController::class, 'store']);
            Route::get('/board', [TicketController::class, 'board']);
            Route::get('/timeline', [TicketController::class, 'timeline']);
            Route::get('/priorities', [TicketController::class, 'priorities']);
            Route::patch('/{id}/status', [TicketController::class, 'updateStatus']);
            Route::patch('/{id}/done', [TicketController::class, 'markAsDone']);
            Route::get('/{id}', [TicketController::class, 'show']);
        });

        Route::get('/epic', [TicketController::class, 'epic']);

        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index']);
            Route::get('/{id}', [PermissionController::class, 'show']);
            Route::post('/', [PermissionController::class, 'store']);
        });

        Route::prefix('face')->group(function () {
            Route::post('/register', [FaceRecognitionController::class, 'registerFace']);
            Route::get('/check', [FaceRecognitionController::class, 'checkFace']);
            Route::delete('/{userId}', [FaceRecognitionController::class, 'deleteFace']);
        });

        Route::post('/attendance/checkin-face', [FaceRecognitionController::class, 'checkInFace']);
        Route::post('/attendance/checkout-face', [FaceRecognitionController::class, 'checkOutFace']);

        Route::prefix('summary')->group(function () {
            Route::get('/', [DashboardController::class, 'overview']);
            Route::get('/my-projects', [DashboardController::class, 'overviewMyProjects']);
            Route::get('/my-tickets', [DashboardController::class, 'overviewMyTickets']);
            Route::get('/created-tickets', [DashboardController::class, 'overviewCreatedTickets']);
        });

        Route::prefix('settings')->group(function () {
            Route::get('/', [SystemController::class, 'settings']);
            Route::get('/system', [SystemController::class, 'systemSettings']);
        });
    });
});


Route::prefix('whatsapp')->middleware('throttle:' . config('api.throttle.default', '60,1'))->group(function () {
    Route::get('/webhook', [WhatsappWebhookController::class, 'verify']);
    Route::post('/webhook', [WhatsappWebhookController::class, 'handle']);
    Route::get('/media/{mediaId}', [\App\Http\Controllers\Api\WhatsappMediaController::class, 'show']);
});
