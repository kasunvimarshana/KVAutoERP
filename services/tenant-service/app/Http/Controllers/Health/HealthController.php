<?php

declare(strict_types=1);

namespace App\Http\Controllers\Health;

use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    use ApiResponse;

    /**
     * GET /health
     */
    public function index(): JsonResponse
    {
        return $this->success([
            'status'    => 'ok',
            'service'   => 'tenant-service',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * GET /health/detailed
     */
    public function detailed(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache'    => $this->checkCache(),
        ];

        $allHealthy = collect($checks)->every(fn ($c) => $c['status'] === 'ok');

        return response()->json([
            'success'   => $allHealthy,
            'status'    => $allHealthy ? 'ok' : 'degraded',
            'service'   => 'tenant-service',
            'checks'    => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $allHealthy ? 200 : 503);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'ok', 'latency_ms' => $this->measureMs(fn () => DB::select('SELECT 1'))];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . uniqid('', true);
            Cache::put($key, 1, 5);
            Cache::forget($key);

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function measureMs(callable $fn): float
    {
        $start = microtime(true);
        $fn();

        return round((microtime(true) - $start) * 1000, 2);
    }
}
