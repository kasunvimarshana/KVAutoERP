<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Shared\Base\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Health Check Controller.
 *
 * Provides liveness and readiness endpoints for container orchestration
 * (Kubernetes, ECS, etc.) and monitoring systems.
 */
final class HealthController extends BaseController
{
    /**
     * Full health check — verifies all critical dependencies.
     *
     * GET /health
     */
    public function index(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache'    => $this->checkCache(),
            'status'   => 'healthy',
            'service'  => 'auth-service',
            'version'  => config('app.version', '1.0.0'),
            'time'     => now()->toIso8601String(),
        ];

        $allHealthy = $checks['database']['status'] === 'ok'
            && $checks['cache']['status'] === 'ok';

        return response()->json(
            data: [
                'success' => $allHealthy,
                'message' => $allHealthy ? 'All systems operational' : 'Degraded — one or more checks failed',
                'data'    => $checks,
                'meta'    => ['request_id' => request()->header('X-Request-ID')],
                'errors'  => [],
            ],
            status: $allHealthy ? 200 : 503,
        );
    }

    /**
     * Quick liveness probe — returns immediately without checking dependencies.
     *
     * GET /health/ping
     */
    public function ping(): JsonResponse
    {
        return $this->success(
            data: [
                'service' => 'auth-service',
                'status'  => 'ok',
                'time'    => now()->toIso8601String(),
            ],
            message: 'pong',
        );
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Verify the primary database connection is reachable.
     *
     * @return array{status: string, latency_ms: float|null, error?: string}
     */
    private function checkDatabase(): array
    {
        $start = microtime(true);

        try {
            DB::connection()->getPdo();

            return [
                'status'     => 'ok',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'error'  => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify the cache store (Redis) is reachable.
     *
     * @return array{status: string, latency_ms: float|null, error?: string}
     */
    private function checkCache(): array
    {
        $start = microtime(true);

        try {
            $key = 'health:' . uniqid('', true);
            Cache::put($key, 'ok', 5);
            Cache::forget($key);

            return [
                'status'     => 'ok',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'error'  => 'Cache connection failed: ' . $e->getMessage(),
            ];
        }
    }
}
