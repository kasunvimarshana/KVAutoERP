<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Add a unique X-Request-ID to every request/response cycle for distributed tracing.
 */
class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $requestId = $request->header('X-Request-ID') ?: 'req_' . Str::uuid()->toString();

        // Make the ID available on the request for downstream access
        $request->headers->set('X-Request-ID', $requestId);

        $response = $next($request);

        $response->headers->set('X-Request-ID', $requestId);

        Log::withContext(['request_id' => $requestId]);

        return $response;
    }
}
