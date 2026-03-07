<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $dbStatus = 'ok';

        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $dbStatus = 'error';
        }

        $status = $dbStatus === 'ok' ? 200 : 503;

        return response()->json([
            'success'   => $dbStatus === 'ok',
            'service'   => 'user-service',
            'status'    => $dbStatus === 'ok' ? 'healthy' : 'unhealthy',
            'checks'    => [
                'database' => $dbStatus,
            ],
            'timestamp' => now()->toIso8601String(),
        ], $status);
    }
}
