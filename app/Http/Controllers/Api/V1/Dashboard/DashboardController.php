<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Attendance;
use App\Models\Notification;
use App\Models\PermittedAbsence;
use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends BaseApiController
{
    /**
    * Main dashboard payload.
     * Endpoint: GET /api/v1/dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $attendanceQuery = Attendance::query()
            ->where('user_id', $user->id)
            ->whereYear('attendance_date', $currentYear)
            ->whereMonth('attendance_date', $currentMonth);

        $assignedTicketsQuery = Ticket::query()
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id));

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'today_attendance' => Attendance::query()
                ->where('user_id', $user->id)
                ->whereDate('attendance_date', today())
                ->first(),
            'metrics' => [
                'projects' => Project::query()->whereHas('members', fn ($q) => $q->where('users.id', $user->id))->count(),
                'assigned_tickets_open' => (clone $assignedTicketsQuery)
                    ->whereHas('status', fn ($q) => $q->where('is_completed', false))
                    ->count(),
                'assigned_tickets_completed' => (clone $assignedTicketsQuery)
                    ->whereHas('status', fn ($q) => $q->where('is_completed', true))
                    ->count(),
                'unread_notifications' => Notification::query()->where('user_id', $user->id)->whereNull('read_at')->count(),
                'pending_permissions' => PermittedAbsence::query()->where('user_id', $user->id)->where('status', 'pending')->count(),
                'attendance_days_this_month' => (clone $attendanceQuery)->count(),
                'late_days_this_month' => (clone $attendanceQuery)->where('status', 'late')->count(),
            ],
        ]);
    }

    /**
        * Greeting message for dashboard.
     * Endpoint: GET /api/v1/dashboard/greeting
     */
    public function greeting(Request $request): JsonResponse
    {
        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour < 11 => 'Good morning',
            $hour < 15 => 'Good afternoon',
            $hour < 19 => 'Good evening',
            default => 'Good night',
        };

        return $this->success([
            'text' => $greeting . ', ' . ($request->user()?->name ?? 'User'),
        ]);
    }

    /**
     * Server time information.
     * Endpoint: GET /api/v1/dashboard/time
     */
    public function time(): JsonResponse
    {
        return $this->success([
            'now' => now()->toISOString(),
            'date' => now()->toDateString(),
            'timezone' => config('app.timezone'),
        ]);
    }

    /**
        * Dashboard summary statistics.
     * Endpoint: GET /api/v1/dashboard/statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $year = (int) $request->integer('year', now()->year);
        $month = (int) $request->integer('month', now()->month);

        $attendance = Attendance::query()
            ->where('user_id', $userId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month);

        $tickets = Ticket::query()->whereHas('assignees', fn ($q) => $q->where('users.id', $userId));

        return $this->success([
            'summary' => [],
            'attendance' => [
                'present' => (clone $attendance)->where('status', 'present')->count(),
                'late' => (clone $attendance)->where('status', 'late')->count(),
                'absent' => (clone $attendance)->where('status', 'absent')->count(),
                'leave' => (clone $attendance)->where('status', 'leave')->count(),
            ],
            'tickets' => [
                'total' => (clone $tickets)->count(),
                'completed' => (clone $tickets)->whereHas('status', fn ($q) => $q->where('is_completed', true))->count(),
                'open' => (clone $tickets)->whereHas('status', fn ($q) => $q->where('is_completed', false))->count(),
            ],
            'period' => [
                'year' => $year,
                'month' => $month,
            ],
        ]);
    }

    /**
        * Main summary endpoint.
     * Endpoint: GET /api/v1/overview
     */
    public function overview(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    /**
        * My project summary.
     * Endpoint: GET /api/v1/overview/my-projects
     */
    public function overviewMyProjects(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $projects = Project::query()
            ->whereHas('members', fn ($q) => $q->where('users.id', $userId))
            ->withCount('tickets')
            ->withCount([
                'tickets as completed_tickets_count' => fn ($q) => $q->whereHas('status', fn ($sq) => $sq->where('is_completed', true)),
            ])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function (Project $project) {
                $progress = $project->tickets_count > 0
                    ? round(($project->completed_tickets_count / $project->tickets_count) * 100, 2)
                    : 0;

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'status' => $project->status,
                    'start_date' => optional($project->start_date)?->toDateString(),
                    'end_date' => optional($project->end_date)?->toDateString(),
                    'tickets_count' => $project->tickets_count,
                    'completed_tickets_count' => $project->completed_tickets_count,
                    'progress' => $progress,
                ];
            });

        return $this->success($projects);
    }

    /**
        * My ticket summary.
     * Endpoint: GET /api/v1/overview/my-tickets
     */
    public function overviewMyTickets(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $tickets = Ticket::query()
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $userId))
            ->with(['project:id,name', 'status:id,name,is_completed'])
            ->latest()
            ->limit(20)
            ->get();

        return $this->success([
            'total' => $tickets->count(),
            'completed' => $tickets->where('status.is_completed', true)->count(),
            'open' => $tickets->where('status.is_completed', false)->count(),
            'items' => $tickets,
        ]);
    }

    /**
        * Summary of created tickets.
     * Endpoint: GET /api/v1/overview/created-tickets
     */
    public function overviewCreatedTickets(Request $request): JsonResponse
    {
        $tickets = Ticket::query()
            ->where('created_by', $request->user()->id)
            ->with(['project:id,name', 'status:id,name,is_completed'])
            ->latest()
            ->limit(20)
            ->get();

        return $this->success([
            'total' => $tickets->count(),
            'completed' => $tickets->where('status.is_completed', true)->count(),
            'open' => $tickets->where('status.is_completed', false)->count(),
            'items' => $tickets,
        ]);
    }
}
