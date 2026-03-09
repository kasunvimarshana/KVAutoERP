<?php

declare(strict_types=1);

namespace App\Support\Health;

use App\Contracts\Health\HealthCheckInterface;
use App\Contracts\Health\HealthCheckResult;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CacheHealthCheck implements HealthCheckInterface
{
    private const PROBE_KEY = 'health:probe';

    public function name(): string
    {
        return 'cache';
    }

    public function check(): HealthCheckResult
    {
        $start = microtime(true);

        try {
            $probe = uniqid('probe_', true);
            Cache::put(self::PROBE_KEY, $probe, 10);
            $retrieved  = Cache::get(self::PROBE_KEY);
            $responseMs = (microtime(true) - $start) * 1000;

            if ($retrieved !== $probe) {
                return HealthCheckResult::unhealthy(
                    $this->name(),
                    'Cache read/write round-trip failed: values did not match.',
                    ['driver' => config('cache.default')],
                    round($responseMs, 2),
                );
            }

            Cache::forget(self::PROBE_KEY);

            return HealthCheckResult::healthy(
                $this->name(),
                'Cache is healthy.',
                ['driver' => config('cache.default')],
                round($responseMs, 2),
            );
        } catch (Throwable $e) {
            $responseMs = (microtime(true) - $start) * 1000;

            return HealthCheckResult::unhealthy(
                $this->name(),
                "Cache check failed: {$e->getMessage()}",
                ['driver' => config('cache.default'), 'exception' => $e::class],
                round($responseMs, 2),
            );
        }
    }
}
