<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Health Check Controller.
 *
 * Provides health check endpoints for container orchestration,
 * load balancers, and monitoring systems.
 */
class HealthController extends Controller
{
    /**
     * Basic liveness probe.
     *
     * GET /api/health
     */
    public function check(): JsonResponse
    {
        return response()->json([
            'status'    => 'healthy',
            'service'   => config('app.name'),
            'version'   => config('app.version', '1.0.0'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Deep readiness probe - checks all dependencies.
     *
     * GET /api/health/ready
     */
    public function ready(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache'    => $this->checkCache(),
        ];

        $isHealthy = !in_array(false, array_column($checks, 'healthy'), true);

        return response()->json([
            'status'    => $isHealthy ? 'ready' : 'not_ready',
            'service'   => config('app.name'),
            'checks'    => $checks,
            'timestamp' => now()->toISOString(),
        ], $isHealthy ? 200 : 503);
    }

    /**
     * Check database connectivity.
     *
     * @return array<string, mixed>
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['healthy' => true, 'message' => 'Connected'];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check cache connectivity.
     *
     * @return array<string, mixed>
     */
    private function checkCache(): array
    {
        try {
            Cache::put('health_check', true, 10);
            $result = Cache::get('health_check');
            return ['healthy' => $result === true, 'message' => 'Connected'];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }
}
