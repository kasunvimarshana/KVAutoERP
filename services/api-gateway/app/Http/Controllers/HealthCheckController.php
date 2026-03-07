<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HealthCheckController extends Controller
{
    /**
     * Services the gateway can route to, keyed by service name.
     */
    private array $services = [
        'auth'          => 'AUTH_SERVICE_URL',
        'tenant'        => 'TENANT_SERVICE_URL',
        'inventory'     => 'INVENTORY_SERVICE_URL',
        'order'         => 'ORDER_SERVICE_URL',
        'notification'  => 'NOTIFICATION_SERVICE_URL',
    ];

    /**
     * Gateway self-check (fast – no downstream calls).
     */
    public function check(): JsonResponse
    {
        return response()->json([
            'service' => config('app.name', 'ApiGateway'),
            'status'  => 'healthy',
            'time'    => now()->toIso8601String(),
        ]);
    }

    /**
     * Aggregate health check – calls /api/v1/health on each downstream service.
     */
    public function checkAll(): JsonResponse
    {
        $results = [];
        $allHealthy = true;

        foreach ($this->services as $name => $envKey) {
            $baseUrl = env($envKey);

            if (!$baseUrl) {
                $results[$name] = ['status' => 'unconfigured'];
                continue;
            }

            $healthUrl = rtrim($baseUrl, '/') . '/api/v1/health';

            try {
                $response = Http::timeout(5)->get($healthUrl);

                $results[$name] = [
                    'status'  => $response->successful() ? 'healthy' : 'unhealthy',
                    'http'    => $response->status(),
                    'details' => $response->json(),
                ];

                if (!$response->successful()) {
                    $allHealthy = false;
                }
            } catch (\Throwable $e) {
                Log::warning("Health check failed for service: {$name}", ['error' => $e->getMessage()]);
                $results[$name] = ['status' => 'unreachable', 'error' => $e->getMessage()];
                $allHealthy = false;
            }
        }

        return response()->json([
            'gateway'  => config('app.name', 'ApiGateway'),
            'status'   => $allHealthy ? 'healthy' : 'degraded',
            'services' => $results,
            'time'     => now()->toIso8601String(),
        ], $allHealthy ? 200 : 503);
    }
}
