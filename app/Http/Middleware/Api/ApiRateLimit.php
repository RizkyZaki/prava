<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
{
    public function __construct(private readonly ThrottleRequests $throttleRequests)
    {
    }

    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        $limits = config('api.throttle', []);
        $limit = $limits[$type] ?? ($limits['default'] ?? '60,1');

        return $this->throttleRequests->handle($request, $next, $limit);
    }
}
