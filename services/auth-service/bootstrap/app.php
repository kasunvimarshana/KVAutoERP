<?php

declare(strict_types=1);

use App\Presentation\Middleware\CheckAbility;
use App\Presentation\Middleware\TenantMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant' => TenantMiddleware::class,
            'ability' => CheckAbility::class,
        ]);

        $middleware->api(append: [
            \Illuminate\Http\Middleware\SetCacheHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\App\Domain\Exceptions\AuthenticationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'AUTHENTICATION_FAILED',
            ], $e->getCode() ?: 401);
        });

        $exceptions->render(function (\App\Domain\Exceptions\TenantException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'TENANT_ERROR',
            ], $e->getCode() ?: 400);
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
            ], 404);
        });
    })
    ->create();
