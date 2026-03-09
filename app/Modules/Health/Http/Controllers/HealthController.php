<?php

declare(strict_types=1);

namespace App\Modules\Health\Http\Controllers;

use App\Core\Contracts\MessageBroker\MessageBrokerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * HealthController
 *
 * Provides standardised health-check and liveness endpoints for:
 *  - Load balancers (GET /health/live)
 *  - Readiness probes (GET /health/ready)
 *  - Orchestrators / monitoring (GET /health)
 */
class HealthController
{
    public function __construct(
        private readonly MessageBrokerInterface $broker
    ) {}

    /**
     * GET /api/v1/health
     *
     * Full health report covering all infrastructure dependencies.
     */
    public function check(): JsonResponse
    {
        $checks = [
            'database'      => $this->checkDatabase(),
            'cache'         => $this->checkCache(),
            'message_broker'=> $this->checkMessageBroker(),
        ];

        $healthy     = collect($checks)->every(fn ($c) => $c['status'] === 'ok');
        $statusCode  = $healthy ? 200 : 503;

        return response()->json([
            'status'    => $healthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'version'   => config('app.version', '1.0.0'),
            'checks'    => $checks,
        ], $statusCode);
    }

    /**
     * GET /api/v1/health/live
     *
     * Kubernetes/ECS liveness probe – returns 200 if the process is running.
     */
    public function live(): JsonResponse
    {
        return response()->json(['status' => 'alive', 'timestamp' => now()->toIso8601String()]);
    }

    /**
     * GET /api/v1/health/ready
     *
     * Kubernetes/ECS readiness probe – returns 200 when the service can accept traffic.
     */
    public function ready(): JsonResponse
    {
        $dbOk    = $this->checkDatabase()['status'] === 'ok';
        $cacheOk = $this->checkCache()['status'] === 'ok';

        $ready = $dbOk && $cacheOk;

        return response()->json(
            ['status' => $ready ? 'ready' : 'not_ready'],
            $ready ? 200 : 503
        );
    }

    // -------------------------------------------------------------------------
    //  Individual checks
    // -------------------------------------------------------------------------

    /** @return array{status: string, latency_ms?: int, error?: string} */
    private function checkDatabase(): array
    {
        $start = hrtime(true);

        try {
            DB::connection()->getPdo();
            $latency = (int) ((hrtime(true) - $start) / 1_000_000);
            return ['status' => 'ok', 'latency_ms' => $latency];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }

    /** @return array{status: string, driver?: string, error?: string} */
    private function checkCache(): array
    {
        try {
            $key = '_health_check_' . uniqid();
            Cache::put($key, 'ok', 5);
            $value = Cache::get($key);
            Cache::forget($key);

            return [
                'status' => $value === 'ok' ? 'ok' : 'error',
                'driver' => config('cache.default'),
            ];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }

    /** @return array{status: string, driver?: string, error?: string} */
    private function checkMessageBroker(): array
    {
        try {
            $healthy = $this->broker->isHealthy();
            return [
                'status' => $healthy ? 'ok' : 'error',
                'driver' => config('saas.message_broker.driver', 'database'),
            ];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }
}
