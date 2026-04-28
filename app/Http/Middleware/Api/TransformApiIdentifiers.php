<?php

namespace App\Http\Middleware\Api;

use App\Support\ApiIdentifierTransformer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TransformApiIdentifiers
{
    public function handle(Request $request, Closure $next): Response
    {
        ApiIdentifierTransformer::handleRequest($request);

        $response = $next($request);

        return ApiIdentifierTransformer::handleResponse($response);
    }
}
