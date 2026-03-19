<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies inbound service-to-service requests using a shared API key.
 *
 * Other microservices (e.g. Auth Service) include the `X-Service-Key` header
 * when calling internal user-service endpoints (e.g. /api/internal/v1/...).
 *
 * The comparison uses hash_equals() to prevent timing attacks.
 */
final class VerifyServiceKeyMiddleware
{
    /** Header name carrying the service API key. */
    private const SERVICE_KEY_HEADER = 'X-Service-Key';

    /**
     * Handle an incoming request.
     *
     * @param  Request                        $request
     * @param  Closure(Request): Response     $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $provided = (string) $request->header(self::SERVICE_KEY_HEADER, '');
        $expected = (string) config('user_service.service_key', '');

        if ($expected === '' || !hash_equals($expected, $provided)) {
            return ApiResponse::unauthorized('Invalid or missing service key.');
        }

        $request->attributes->set('service_authenticated', true);

        return $next($request);
    }
}
