<?php

namespace App\Http\Controllers\Api\V1\Permission;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\PermittedAbsence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PermissionController extends BaseApiController
{
    /**
     * Permission request list.
     * Endpoint: GET /api/v1/permissions
     */
    public function index(Request $request): JsonResponse
    {
        $items = PermittedAbsence::query()
            ->where('user_id', $request->user()->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', (string) $request->string('status')))
            ->orderByDesc('created_at')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->paginated($items);
    }

    /**
     * Permission request details.
     * Endpoint: GET /api/v1/permissions/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $item = PermittedAbsence::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$item) {
            return $this->notFound('Permission request not found');
        }

        return $this->success($item);
    }

    /**
     * Submit a permission request.
     * Endpoint: POST /api/v1/permissions
     */
    public function store(Request $request): JsonResponse
    {
        $minStartDate = now('Asia/Jakarta')->addDay()->toDateString();
        $validated = $request->validate([
            'type' => ['required', 'in:izin,sakit,remote'],
            'reason' => ['required', 'string', 'max:1000'],
            'start_date' => ['required', 'date', 'after_or_equal:' . $minStartDate],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'attachment' => ['required_if:type,izin,sakit', 'nullable', 'file', 'mimes:jpeg,png,gif,webp,pdf', 'max:2048'],
        ], [
            'start_date.after_or_equal' => 'Tanggal mulai minimal H-1 dari hari ini.',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('permitted-absences', 'public');
        }

        $created = PermittedAbsence::query()->create([
            'user_id' => $request->user()->id,
            'absence_type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return $this->success([
            'id' => $created->id,
            'submitted_at' => optional($created->created_at)?->toISOString(),
            'payload' => $created,
        ], 'Permission request submitted', 201);
    }
}
