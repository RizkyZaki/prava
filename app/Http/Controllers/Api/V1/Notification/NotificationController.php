<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseApiController
{
    /**
     * User notification list.
     * Endpoint: GET /api/v1/notifications
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at');

        if ($request->filled('unread')) {
            $unread = $request->boolean('unread');
            $query->when($unread, fn ($q) => $q->whereNull('read_at'))
                ->when(!$unread, fn ($q) => $q->whereNotNull('read_at'));
        }

        $notifications = $query->paginate((int) $request->integer('per_page', 15));

        return $this->paginated($notifications);
    }

    /**
     * Mark notification as read.
     * Endpoint: PATCH /api/v1/notifications/{id}/read
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->notFound('Notification not found');
        }

        if (is_null($notification->read_at)) {
            $notification->read_at = now();
            $notification->save();
        }

        return $this->success([
            'id' => $id,
            'read_at' => optional($notification->read_at)?->toISOString(),
        ], 'Notification marked as read');
    }

    /**
     * Delete notification.
     * Endpoint: DELETE /api/v1/notifications/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $notification = Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return $this->notFound('Notification not found');
        }

        $notification->delete();

        return $this->success([
            'id' => $id,
        ], 'Notification deleted');
    }
}
