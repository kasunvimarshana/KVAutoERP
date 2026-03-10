<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'service' => 'user-service',
            'version' => config('app.version', '1.0.0'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function ready(): JsonResponse
    {
        $checks = [];
        $isHealthy = true;

        try {
            DB::connection()->getPdo();
            $checks['database'] = ['status' => 'healthy'];
        } catch (\Exception $e) {
            $checks['database'] = ['status' => 'unhealthy', 'error' => $e->getMessage()];
            $isHealthy = false;
        }

        try {
            Cache::set('health_check', true, 10);
            $checks['cache'] = ['status' => 'healthy'];
        } catch (\Exception $e) {
            $checks['cache'] = ['status' => 'degraded'];
        }

        return response()->json([
            'status' => $isHealthy ? 'ready' : 'not_ready',
            'service' => 'user-service',
            'checks' => $checks,
            'timestamp' => now()->toISOString(),
        ], $isHealthy ? 200 : 503);
    }
}
