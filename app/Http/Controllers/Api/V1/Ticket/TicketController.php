<?php

namespace App\Http\Controllers\Api\V1\Ticket;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Epic;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
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
     * Ticket board (kanban columns + cards).
     * Endpoint: GET /api/v1/ticket/board
     */
    public function board(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
        ]);

        $user = $request->user();

        $project = Project::query()
            ->where('id', $validated['project_id'])
            ->whereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->first();

        if (! $project) {
            return $this->notFound('Project not found or inaccessible');
        }

        $statuses = TicketStatus::query()
            ->where('project_id', $project->id)
            ->orderBy('sort_order')
            ->with([
                'tickets' => fn ($q) => $q
                    ->with(['priority:id,name,color', 'assignees:id,name,email'])
                    ->orderByDesc('updated_at'),
            ])
            ->get()
            ->map(function (TicketStatus $status) {
                return [
                    'id' => $status->id,
                    'name' => $status->name,
                    'color' => $status->color,
                    'sort_order' => $status->sort_order,
                    'is_completed' => $status->is_completed,
                    'tickets' => $status->tickets->map(function (Ticket $ticket) {
                        return [
                            'id' => $ticket->id,
                            'uuid' => $ticket->uuid,
                            'name' => $ticket->name,
                            'description' => $ticket->description,
                            'start_date' => optional($ticket->start_date)?->toDateString(),
                            'due_date' => optional($ticket->due_date)?->toDateString(),
                            'priority' => $ticket->priority,
                            'assignees' => $ticket->assignees,
                            'updated_at' => optional($ticket->updated_at)?->toISOString(),
                        ];
                    })->values(),
                ];
            })
            ->values();

        return $this->success([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
            'columns' => $statuses,
        ], 'Success', 200, [
            'labels' => $this->ticketBoardLabels(),
        ]);
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

        return $this->success($ticket, 'Success', 200, [
            'labels' => $this->ticketEntityLabels(),
        ]);
    }

    /**
     * Create a new ticket.
     * Endpoint: POST /api/v1/ticket
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'ticket_status_id' => ['nullable', 'integer', 'exists:ticket_statuses,id'],
            'priority_id' => ['nullable', 'integer', 'exists:ticket_priorities,id'],
            'epic_id' => ['nullable', 'integer', 'exists:epics,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'assignee_ids' => ['nullable', 'array'],
            'assignee_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $user = $request->user();

        $project = Project::query()
            ->where('id', $validated['project_id'])
            ->whereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->with('members:id')
            ->first();

        if (! $project) {
            return $this->notFound('Project not found or inaccessible');
        }

        $statusId = $validated['ticket_status_id'] ?? null;
        if (! $statusId) {
            $statusId = TicketStatus::query()
                ->where('project_id', $project->id)
                ->orderBy('sort_order')
                ->value('id');
        }

        if (! $statusId) {
            return $this->error('Ticket status is not configured for this project', 422);
        }

        $statusBelongsToProject = TicketStatus::query()
            ->where('id', $statusId)
            ->where('project_id', $project->id)
            ->exists();

        if (! $statusBelongsToProject) {
            return $this->error('Selected status does not belong to this project', 422);
        }

        if (! empty($validated['epic_id'])) {
            $epicBelongsToProject = Epic::query()
                ->where('id', $validated['epic_id'])
                ->where('project_id', $project->id)
                ->exists();

            if (! $epicBelongsToProject) {
                return $this->error('Selected epic does not belong to this project', 422);
            }
        }

        $assigneeIds = collect($validated['assignee_ids'] ?? [$user->id])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $projectMemberIds = $project->members->pluck('id');
        $invalidAssignees = $assigneeIds->diff($projectMemberIds)->values();
        if ($invalidAssignees->isNotEmpty()) {
            return $this->error('All assignees must be project members', 422, [
                'invalid_assignee_ids' => $invalidAssignees,
            ]);
        }

        $ticket = Ticket::query()->create([
            'project_id' => $project->id,
            'ticket_status_id' => $statusId,
            'priority_id' => $validated['priority_id'] ?? null,
            'epic_id' => $validated['epic_id'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'created_by' => $user->id,
        ]);

        $ticket->assignUsers($assigneeIds->all());

        $ticket->load([
            'project:id,name',
            'status:id,name,color,is_completed',
            'priority:id,name,color',
            'assignees:id,name,email',
            'creator:id,name,email',
            'epic:id,name',
        ]);

        return $this->success($ticket, 'Ticket created successfully', 201, [
            'labels' => $this->ticketEntityLabels(),
        ]);
    }

    /**
     * Move ticket to another status (kanban drag/drop).
     * Endpoint: PATCH /api/v1/ticket/{id}/status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'ticket_status_id' => ['required', 'integer', 'exists:ticket_statuses,id'],
        ]);

        $ticket = Ticket::query()
            ->where('id', $id)
            ->whereHas('project.members', fn ($q) => $q->where('users.id', $request->user()->id))
            ->first();

        if (! $ticket) {
            return $this->notFound('Ticket not found');
        }

        $targetStatus = TicketStatus::query()->find($validated['ticket_status_id']);
        if (! $targetStatus || $targetStatus->project_id !== $ticket->project_id) {
            return $this->error('Selected status does not belong to this ticket project', 422);
        }

        $ticket->update([
            'ticket_status_id' => $targetStatus->id,
        ]);

        $ticket->load(['status:id,name,color,is_completed', 'project:id,name']);

        return $this->success($ticket, 'Ticket status updated successfully', 200, [
            'labels' => $this->ticketStatusUpdateLabels(),
        ]);
    }

    /**
     * Mark ticket as done by moving it to a completed status.
     * Endpoint: PATCH /api/v1/ticket/{id}/done
     */
    public function markAsDone(Request $request, int $id): JsonResponse
    {
        $ticket = Ticket::query()
            ->where('id', $id)
            ->whereHas('project.members', fn ($q) => $q->where('users.id', $request->user()->id))
            ->first();

        if (! $ticket) {
            return $this->notFound('Ticket not found');
        }

        $completedStatus = TicketStatus::query()
            ->where('project_id', $ticket->project_id)
            ->where('is_completed', true)
            ->orderBy('sort_order')
            ->first();

        if (! $completedStatus) {
            return $this->error('Completed status is not configured for this project', 422);
        }

        $ticket->update([
            'ticket_status_id' => $completedStatus->id,
        ]);

        $ticket->load(['status:id,name,color,is_completed', 'project:id,name']);

        return $this->success($ticket, 'Ticket marked as done', 200, [
            'labels' => $this->ticketStatusUpdateLabels(),
        ]);
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

    private function ticketBoardLabels(): array
    {
        return [
            'project.id' => 'Project ID',
            'columns[].id' => 'Ticket status ID',
            'columns[].sort_order' => 'Ticket column order in board',
            'columns[].tickets[].id' => 'Ticket ID',
            'columns[].tickets[].priority.id' => 'Ticket priority ID',
            'columns[].tickets[].assignees[].id' => 'Assigned user ID',
        ];
    }

    private function ticketEntityLabels(): array
    {
        return [
            'id' => 'Ticket ID',
            'project.id' => 'Project ID',
            'status.id' => 'Ticket status ID',
            'priority.id' => 'Ticket priority ID',
            'assignees[].id' => 'Assigned user ID',
            'creator.id' => 'Ticket creator user ID',
            'epic.id' => 'Epic ID',
        ];
    }

    private function ticketStatusUpdateLabels(): array
    {
        return [
            'id' => 'Ticket ID',
            'ticket_status_id' => 'Current ticket status ID',
            'status.id' => 'Current ticket status ID',
            'project.id' => 'Project ID',
        ];
    }
}
