<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    public function health(): JsonResponse
    {
        $checks = [];

        // Database
        try {
            DB::connection()->getPdo();
            $checks['database'] = ['status' => 'ok'];
        } catch (\Throwable $e) {
            $checks['database'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        // Redis
        try {
            Redis::ping();
            $checks['redis'] = ['status' => 'ok'];
        } catch (\Throwable $e) {
            $checks['redis'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        $allOk      = collect($checks)->every(fn ($c) => $c['status'] === 'ok');
        $statusCode = $allOk ? 200 : 503;

        return response()->json([
            'status'  => $allOk ? 'healthy' : 'degraded',
            'service' => 'inventory-service',
            'checks'  => $checks,
            'time'    => now()->toISOString(),
        ], $statusCode);
    }

    public function ping(): JsonResponse
    {
        return response()->json(['pong' => true, 'time' => now()->toISOString()]);
    }
}
