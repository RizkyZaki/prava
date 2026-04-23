<?php

namespace App\Http\Controllers\Api\V1\System;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemController extends BaseApiController
{
    /**
    * Server status endpoint for health checks.
     * Endpoint: GET /api/v1/status-server
     */
    public function status(): JsonResponse
    {
        return $this->success([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'server' => 'PRAVA ERP',
          ], 'Server is active');
    }

    /**
      * Application and API version information.
     * Endpoint: GET /api/v1/versi
     */
    public function version(): JsonResponse
    {
        return $this->success([
            'version' => config('app.version', '1.0.0'),
            'api_version' => config('api.version', 'v1'),
            'env' => app()->environment(),
        ]);
    }

    /**
        * Safe configuration values for clients.
     * Endpoint: GET /api/v1/config
     */
    public function config(): JsonResponse
    {
        return $this->success([
            'app_name' => config('app.name'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ]);
    }

    /**
        * Get general application settings.
     * Endpoint: GET /api/v1/settings
     */
    public function settings(Request $request): JsonResponse
    {
        $items = Setting::query()
            ->where('user_id', $request->user()->id)
            ->orWhereNull('user_id')
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        return $this->success($items);
    }

    /**
        * Get system settings.
     * Endpoint: GET /api/v1/settings/system
     */
    public function systemSettings(Request $request): JsonResponse
    {
        $items = Setting::query()
            ->whereNull('user_id')
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        return $this->success($items);
    }
}
