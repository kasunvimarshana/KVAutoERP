<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    /**
     * Return the service health status including DB and Redis connectivity.
     */
    public function check(): JsonResponse
    {
        $checks  = [];
        $healthy = true;

        // --- Database ---
        try {
            DB::connection()->getPdo();
            $checks['database'] = ['status' => 'ok'];
        } catch (\Throwable $e) {
            $checks['database'] = ['status' => 'error', 'message' => $e->getMessage()];
            $healthy = false;
        }

        // --- Redis / Cache ---
        try {
            $key = 'health_check_' . uniqid();
            Cache::put($key, 'ok', 5);
            $value = Cache::get($key);
            Cache::forget($key);

            if ($value === 'ok') {
                $checks['redis'] = ['status' => 'ok'];
            } else {
                $checks['redis'] = ['status' => 'error', 'message' => 'Cache read/write mismatch'];
                $healthy = false;
            }
        } catch (\Throwable $e) {
            $checks['redis'] = ['status' => 'error', 'message' => $e->getMessage()];
            $healthy = false;
        }

        $statusCode = $healthy ? 200 : 503;

        return response()->json([
            'service' => config('app.name', 'TenantService'),
            'status'  => $healthy ? 'healthy' : 'unhealthy',
            'checks'  => $checks,
            'time'    => now()->toIso8601String(),
        ], $statusCode);
    }
}
