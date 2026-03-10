<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Health Check Controller
 * 
 * Provides health check endpoints for load balancers, K8s probes, etc.
 */
class HealthController extends Controller
{
    /**
     * GET /health
     * 
     * Basic health check (liveness probe).
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'service' => 'auth-service',
            'version' => config('app.version', '1.0.0'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * GET /health/ready
     * 
     * Readiness probe - checks all dependencies.
     */
    public function ready(): JsonResponse
    {
        $checks = [];
        $isHealthy = true;

        // Check database
        try {
            DB::connection()->getPdo();
            $checks['database'] = ['status' => 'healthy', 'latency_ms' => 0];
        } catch (\Exception $e) {
            $checks['database'] = ['status' => 'unhealthy', 'error' => $e->getMessage()];
            $isHealthy = false;
        }

        // Check cache
        try {
            $start = microtime(true);
            Cache::set('health_check', true, 10);
            Cache::get('health_check');
            $checks['cache'] = ['status' => 'healthy', 'latency_ms' => round((microtime(true) - $start) * 1000, 2)];
        } catch (\Exception $e) {
            $checks['cache'] = ['status' => 'degraded', 'error' => $e->getMessage()];
            // Cache degradation doesn't fail readiness
        }

        return response()->json([
            'status' => $isHealthy ? 'ready' : 'not_ready',
            'service' => 'auth-service',
            'checks' => $checks,
            'timestamp' => now()->toISOString(),
        ], $isHealthy ? 200 : 503);
    }
}
