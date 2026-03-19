<?php

use App\Exceptions\AuthenticationException;
use App\Http\Middleware\RequireAbacPolicy;
use App\Http\Middleware\RequirePermission;
use App\Http\Middleware\VerifyJwtToken;
use App\Http\Middleware\VerifyServiceToken;
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
            'auth.jwt'             => VerifyJwtToken::class,
            'verify.service.token' => VerifyServiceToken::class,
            'require.permission'   => RequirePermission::class,
            'abac'                 => RequireAbacPolicy::class,
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
    })->create();
