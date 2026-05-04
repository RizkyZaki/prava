<?php

namespace App\Traits\Api;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use JsonSerializable;

trait ApiResponseTrait
{
    protected function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        if (!is_null($data)) {
            $response['data'] = $this->normalizeResponseData($data);
        }

        return response()->json($response, $statusCode);
    }

    protected function error(
        string $message = 'An error occurred',
        int $statusCode = 400,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $this->normalizeResponseData($errors);
        }

        return response()->json($response, $statusCode);
    }

    protected function paginated(
        mixed $resource,
        string $message = 'Success'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $this->normalizeResponseData($resource->items()),
            'meta' => [
                'current_page' => $resource->currentPage(),
                'last_page' => $resource->lastPage(),
                'per_page' => $resource->perPage(),
                'total' => $resource->total(),
                'from' => $resource->firstItem(),
                'to' => $resource->lastItem(),
            ],
            'links' => [
                'first' => $resource->url(1),
                'last' => $resource->url($resource->lastPage()),
                'prev' => $resource->previousPageUrl(),
                'next' => $resource->nextPageUrl(),
            ],
        ]);
    }

    protected function normalizeResponseData(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $this->formatDateTime($value);
        }

        if ($value instanceof Arrayable) {
            return $this->normalizeResponseData($value->toArray());
        }

        if ($value instanceof JsonSerializable) {
            return $this->normalizeResponseData($value->jsonSerialize());
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->normalizeResponseData($item);
            }

            return $value;
        }

        if (is_string($value) && $this->isIsoDateTimeString($value)) {
            return $this->formatDateTime(Carbon::parse($value));
        }

        return $value;
    }

    protected function formatDateTime(DateTimeInterface $value): string
    {
        return Carbon::instance($value)
            ->setTimezone('Asia/Jakarta')
            ->format('Y-m-d H:i:s') . ' WIB';
    }

    protected function isIsoDateTimeString(string $value): bool
    {
        return (bool) preg_match(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})?$/',
            $value
        );
    }

    protected function notFound(string $message = 'Data not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    protected function forbidden(string $message = 'Access denied'): JsonResponse
    {
        return $this->error($message, 403);
    }

    protected function validationError(mixed $errors): JsonResponse
    {
        return $this->error('Validation failed', 422, $errors);
    }
}
