<?php

namespace App\Http\Controllers\Api\V1\Ticket;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Epic;
use App\Models\Ticket;
use App\Models\TicketPriority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends BaseApiController
{
    /**
    * Ticket list.
     * Endpoint: GET /api/v1/ticket
     */
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::query()
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $request->user()->id))
            ->with(['project:id,name', 'status:id,name,is_completed', 'priority:id,name,color']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->integer('project_id'));
        }

        if ($request->filled('status_id')) {
            $query->where('ticket_status_id', $request->integer('status_id'));
        }

        $tickets = $query->latest()->paginate((int) $request->integer('per_page', 15));

        return $this->paginated($tickets);
    }

    /**
        * Ticket timeline.
     * Endpoint: GET /api/v1/ticket/timeline
     */
    public function timeline(Request $request): JsonResponse
    {
        $tickets = Ticket::query()
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $request->user()->id))
            ->with(['project:id,name', 'status:id,name'])
            ->orderBy('due_date')
            ->limit((int) $request->integer('limit', 50))
            ->get()
            ->map(function (Ticket $ticket) {
                return [
                    'id' => $ticket->id,
                    'uuid' => $ticket->uuid,
                    'name' => $ticket->name,
                    'project' => $ticket->project?->name,
                    'status' => $ticket->status?->name,
                    'start_date' => optional($ticket->start_date)?->toDateString(),
                    'due_date' => optional($ticket->due_date)?->toDateString(),
                ];
            });

        return $this->success($tickets);
    }

    /**
        * Ticket priorities.
     * Endpoint: GET /api/v1/ticket/prioritas
     */
    public function priorities(): JsonResponse
    {
        $priorities = TicketPriority::query()
            ->withCount('tickets')
            ->orderBy('name')
            ->get();

        return $this->success($priorities);
    }

    /**
        * Ticket details.
     * Endpoint: GET /api/v1/ticket/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $ticket = Ticket::query()
            ->where('id', $id)
            ->whereHas('assignees', fn ($q) => $q->where('users.id', $request->user()->id))
            ->with([
                'project:id,name',
                'status:id,name,color,is_completed',
                'priority:id,name,color',
                'assignees:id,name,email',
                'creator:id,name,email',
                'epic:id,name',
            ])
            ->first();

        if (!$ticket) {
            return $this->notFound('Ticket not found');
        }

        return $this->success($ticket);
    }

    /**
        * Epic list.
     * Endpoint: GET /api/v1/epic
     */
    public function epic(Request $request): JsonResponse
    {
        $projectId = $request->integer('project_id');

        $query = Epic::query()->withCount('tickets');

        if ($projectId > 0) {
            $query->where('project_id', $projectId);
        }

        return $this->success($query->orderBy('sort_order')->get());
    }
}
