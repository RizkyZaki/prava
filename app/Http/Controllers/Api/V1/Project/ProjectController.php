<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends BaseApiController
{
    /**
    * Project list.
     * Endpoint: GET /api/v1/project
     */
    public function index(Request $request): JsonResponse
    {
        $projects = Project::query()
            ->whereHas('members', fn ($q) => $q->where('users.id', $request->user()->id))
            ->withCount('tickets')
            ->latest()
            ->paginate((int) $request->integer('per_page', 15));

        return $this->paginated($projects);
    }

    /**
        * Project timeline.
     * Endpoint: GET /api/v1/project/timeline
     */
    public function timeline(Request $request): JsonResponse
    {
        $projects = Project::query()
            ->whereHas('members', fn ($q) => $q->where('users.id', $request->user()->id))
            ->orderBy('start_date')
            ->get()
            ->map(function (Project $project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'start_date' => optional($project->start_date)?->toDateString(),
                    'end_date' => optional($project->end_date)?->toDateString(),
                    'status' => $project->status,
                    'progress_percentage' => $project->progress_percentage,
                ];
            });

        return $this->success($projects);
    }

    /**
        * Project board.
     * Endpoint: GET /api/v1/project/board
     */
    public function board(Request $request): JsonResponse
    {
        $projects = Project::query()
            ->whereHas('members', fn ($q) => $q->where('users.id', $request->user()->id))
            ->withCount('tickets')
            ->get()
            ->groupBy('status')
            ->map(fn ($items) => $items->values());

        return $this->success($projects);
    }

    /**
        * Project details.
     * Endpoint: GET /api/v1/project/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $project = Project::query()
            ->where('id', $id)
            ->whereHas('members', fn ($q) => $q->where('users.id', $request->user()->id))
            ->with(['members:id,name,email', 'ticketStatuses:id,project_id,name,color,is_completed'])
            ->withCount('tickets')
            ->first();

        if (!$project) {
            return $this->notFound('Project not found');
        }

        return $this->success($project);
    }
}
