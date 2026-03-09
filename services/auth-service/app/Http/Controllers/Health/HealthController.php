<?php

declare(strict_types=1);

namespace App\Http\Controllers\Health;

use App\Contracts\Health\HealthCheckInterface;
use App\Support\Api\ApiResponse;
use App\Support\Health\CacheHealthCheck;
use App\Support\Health\DatabaseHealthCheck;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class HealthController extends Controller
{
    use ApiResponse;

    /**
     * Quick liveness probe – just confirms the application is up.
     */
    public function index(): JsonResponse
    {
        return $this->success([
            'status'    => 'ok',
            'service'   => 'auth-service',
            'version'   => config('app.version', '1.0.0'),
            'timestamp' => now()->toIso8601String(),
        ], 'Service is running.');
    }

    /**
     * Detailed readiness probe – checks all downstream dependencies.
     */
    public function detailed(): JsonResponse
    {
        /** @var list<HealthCheckInterface> $checks */
        $checks = $this->resolveChecks();

        $results   = [];
        $allHealthy = true;

        foreach ($checks as $check) {
            $result           = $check->check();
            $results[]        = $result->toArray();
            $allHealthy       = $allHealthy && $result->healthy;
        }

        $status = $allHealthy ? 200 : 503;

        return response()->json([
            'success'   => $allHealthy,
            'status'    => $allHealthy ? 'healthy' : 'degraded',
            'service'   => 'auth-service',
            'version'   => config('app.version', '1.0.0'),
            'timestamp' => now()->toIso8601String(),
            'checks'    => $results,
        ], $status);
    }

    /** @return list<HealthCheckInterface> */
    private function resolveChecks(): array
    {
        $checks = [
            new DatabaseHealthCheck(config('database.default', 'mysql')),
            new CacheHealthCheck(),
        ];

        // Message broker check is optional – only include if the binding exists
        if (app()->bound(\App\Contracts\Messaging\MessageBrokerInterface::class)) {
            $checks[] = new \App\Support\Health\MessageBrokerHealthCheck(
                app(\App\Contracts\Messaging\MessageBrokerInterface::class)
            );
        }

        return $checks;
    }
}
