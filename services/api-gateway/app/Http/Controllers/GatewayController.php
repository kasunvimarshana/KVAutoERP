<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GatewayController extends Controller
{
    /**
     * Maps the first path segment to the corresponding service environment key.
     */
    private array $serviceMap = [
        'auth'          => 'AUTH_SERVICE_URL',
        'tenants'       => 'TENANT_SERVICE_URL',
        'inventory'     => 'INVENTORY_SERVICE_URL',
        'orders'        => 'ORDER_SERVICE_URL',
        'notifications' => 'NOTIFICATION_SERVICE_URL',
    ];

    /**
     * Proxy an incoming request to the appropriate downstream microservice.
     */
    public function proxy(Request $request, string $service, string $path = ''): JsonResponse
    {
        $serviceUrl = $this->resolveServiceUrl($service);

        if (!$serviceUrl) {
            return response()->json([
                'error'   => 'Service not found',
                'service' => $service,
            ], 404);
        }

        $requestId = $request->header('X-Request-ID', 'req_' . Str::uuid()->toString());

        // Build the forwarded headers, injecting gateway-specific ones
        $forwardedHeaders = $this->buildForwardedHeaders($request, $requestId);

        $targetUrl = rtrim($serviceUrl, '/') . '/api/v1/' . ltrim($path, '/');

        Log::info('Gateway proxying request', [
            'request_id' => $requestId,
            'service'    => $service,
            'method'     => $request->method(),
            'url'        => $targetUrl,
        ]);

        try {
            $httpClient = Http::withHeaders($forwardedHeaders)
                ->timeout((int) env('GATEWAY_TIMEOUT', 30));

            $response = $httpClient->send($request->method(), $targetUrl, [
                'query'       => $request->query(),
                'json'        => $request->isJson() ? $request->json()->all() : null,
                'form_params' => (!$request->isJson() && in_array($request->method(), ['POST', 'PUT', 'PATCH'], true))
                    ? $request->all()
                    : null,
            ]);

            Log::info('Gateway received response', [
                'request_id' => $requestId,
                'status'     => $response->status(),
            ]);

            return response()->json(
                $response->json() ?? [],
                $response->status()
            )->withHeaders([
                'X-Request-ID'        => $requestId,
                'X-Gateway-Service'   => $service,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gateway connection failed', [
                'service'    => $service,
                'request_id' => $requestId,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'error'      => 'Service unavailable',
                'service'    => $service,
                'request_id' => $requestId,
            ], 503);
        } catch (\Throwable $e) {
            Log::error('Gateway proxy error', [
                'service'    => $service,
                'request_id' => $requestId,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'error'      => 'Gateway error',
                'message'    => $e->getMessage(),
                'request_id' => $requestId,
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function resolveServiceUrl(string $service): ?string
    {
        $envKey = $this->serviceMap[$service] ?? null;
        return $envKey ? env($envKey) : null;
    }

    private function buildForwardedHeaders(Request $request, string $requestId): array
    {
        // Start with all incoming headers, then override/add gateway headers
        $headers = collect($request->headers->all())
            ->map(fn ($v) => is_array($v) ? implode(', ', $v) : $v)
            ->except(['host'])  // Remove host – each service has its own
            ->toArray();

        return array_merge($headers, [
            'X-Gateway-Request'  => 'true',
            'X-Request-ID'       => $requestId,
            'X-Forwarded-For'    => $request->ip(),
            'X-Real-IP'          => $request->ip(),
        ]);
    }
}
