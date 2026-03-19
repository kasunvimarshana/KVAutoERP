<?php

use App\Exceptions\AuthenticationException;
use App\Exceptions\TokenException;
use App\Http\Middleware\RequirePermission;
use App\Http\Middleware\TenantRateLimit;
use App\Http\Middleware\VerifyJwtToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.jwt'           => VerifyJwtToken::class,
            'tenant.rate_limit'  => TenantRateLimit::class,
            'require.permission' => RequirePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e) {
            return new JsonResponse([
                'success' => false,
                'data'    => null,
                'meta'    => [],
                'errors'  => ['authentication' => $e->getMessage()],
                'message' => 'Authentication failed',
            ], 401);
        });

        $exceptions->render(function (TokenException $e) {
            return new JsonResponse([
                'success' => false,
                'data'    => null,
                'meta'    => [],
                'errors'  => ['token' => $e->getMessage()],
                'message' => 'Token error',
            ], 401);
        });
    })->create();

