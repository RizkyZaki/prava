<?php

namespace App\Http\Controllers\Api\V1\Activity;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends BaseApiController
{
    /**
     * Activity list.
     * Endpoint: GET /api/v1/activities
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::query()->with('creator:id,name,email');

        if ($request->filled('type')) {
            $query->where('type', (string) $request->string('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }

        $items = $query->orderBy('start_date')->paginate((int) $request->integer('per_page', 15));

        return $this->paginated($items);
    }

    /**
     * Activity details.
     * Endpoint: GET /api/v1/activities/{id}
     */
    public function show(int $id): JsonResponse
    {
        $event = Event::query()->with('creator:id,name,email')->find($id);

        if (!$event) {
            return $this->notFound('Activity not found');
        }

        return $this->success($event);
    }

    /**
     * Activity calendar.
     * Endpoint: GET /api/v1/activity-calendar
     */
    public function calendar(Request $request): JsonResponse
    {
        $year = (int) $request->integer('year', now()->year);
        $month = (int) $request->integer('month', now()->month);

        $events = Event::query()
            ->forMonth($year, $month)
            ->orderBy('start_date')
            ->get()
            ->map(function (Event $event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'type' => $event->type,
                    'status' => $event->status,
                    'start_date' => optional($event->start_date)?->toISOString(),
                    'end_date' => optional($event->end_date)?->toISOString(),
                    'all_day' => (bool) $event->all_day,
                    'color' => $event->color,
                ];
            });

        return $this->success([
            'year' => $year,
            'month' => $month,
            'items' => $events,
        ]);
    }
}
