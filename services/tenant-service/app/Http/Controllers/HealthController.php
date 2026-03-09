<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Shared\Base\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * Health Controller.
 *
 * Provides liveness and readiness endpoints for orchestrators (Kubernetes, ECS, etc.)
 */
final class HealthController extends BaseController
{
    /**
     * GET /health
     *
     * Full readiness check — verifies all critical dependencies.
     */
    public function index(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis'    => $this->checkRedis(),
        ];

        $allHealthy = !in_array(false, array_column($checks, 'healthy'), strict: true);

        return response()->json([
            'service'   => 'tenant-service',
            'status'    => $allHealthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks'    => $checks,
        ], $allHealthy ? 200 : 503);
    }

    /**
     * GET /health/ping
     *
     * Simple liveness probe — always returns 200 if the process is alive.
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status'    => 'ok',
            'service'   => 'tenant-service',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private checks
    // ──────────────────────────────────────────────────────────────────────

    /**
     * @return array{healthy: bool, message: string, latency_ms: float}
     */
    private function checkDatabase(): array
    {
        $start = microtime(true);

        try {
            DB::statement('SELECT 1');

            return [
                'healthy'    => true,
                'message'    => 'Connected',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            return [
                'healthy'    => false,
                'message'    => $e->getMessage(),
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        }
    }

    /**
     * @return array{healthy: bool, message: string, latency_ms: float}
     */
    private function checkRedis(): array
    {
        $start = microtime(true);

        try {
            Redis::ping();

            return [
                'healthy'    => true,
                'message'    => 'Connected',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            return [
                'healthy'    => false,
                'message'    => $e->getMessage(),
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        }
    }
}
