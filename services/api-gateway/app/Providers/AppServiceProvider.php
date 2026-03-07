<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $tenantId = $request->header('X-Tenant-ID') ?? $request->ip();
            $max      = (int) env('RATE_LIMIT_MAX', 100);
            $decay    = (int) env('RATE_LIMIT_DECAY_MINUTES', 1);

            return Limit::perMinutes($decay, $max)
                ->by($tenantId)
                ->response(function () use ($max) {
                    return response()->json([
                        'error'       => 'Too Many Requests',
                        'retry_after' => 60,
                    ], 429)->withHeaders([
                        'X-RateLimit-Limit'     => $max,
                        'X-RateLimit-Remaining' => 0,
                    ]);
                });
        });
    }
}
