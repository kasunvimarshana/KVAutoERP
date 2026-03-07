<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function health(): JsonResponse
    {
        $checks = [
            'database'     => $this->checkDatabase(),
            'cache'        => $this->checkCache(),
            'passport_keys' => $this->checkPassportKeys(),
        ];

        $healthy    = !in_array(false, array_column($checks, 'healthy'), true);
        $statusCode = $healthy ? 200 : 503;

        return response()->json([
            'status'    => $healthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'service'   => 'auth-service',
            'version'   => config('app.version', '1.0.0'),
            'checks'    => $checks,
        ], $statusCode);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            DB::statement('SELECT 1');

            return ['healthy' => true, 'message' => 'Database connection OK'];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = '_health_check_' . uniqid('', true);
            Cache::put($key, 'ok', 5);
            $value = Cache::get($key);
            Cache::forget($key);

            if ($value !== 'ok') {
                return ['healthy' => false, 'message' => 'Cache read/write mismatch'];
            }

            return ['healthy' => true, 'message' => 'Cache OK'];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => 'Cache error: ' . $e->getMessage()];
        }
    }

    private function checkPassportKeys(): array
    {
        $privateKey = storage_path('oauth-private.key');
        $publicKey  = storage_path('oauth-public.key');

        if (!file_exists($privateKey)) {
            return ['healthy' => false, 'message' => 'Passport private key missing'];
        }

        if (!file_exists($publicKey)) {
            return ['healthy' => false, 'message' => 'Passport public key missing'];
        }

        if (!is_readable($privateKey) || !is_readable($publicKey)) {
            return ['healthy' => false, 'message' => 'Passport keys exist but are not readable'];
        }

        return ['healthy' => true, 'message' => 'Passport keys present and readable'];
    }
}
