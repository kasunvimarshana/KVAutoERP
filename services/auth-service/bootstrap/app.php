<?php

declare(strict_types=1);

use App\Http\Middleware\RateLimitAuthMiddleware;
use App\Http\Middleware\VerifyJwtMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use KvEnterprise\SharedKernel\Http\Middleware\TenantContextMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register named middleware aliases for use in route definitions.
        // TenantContextMiddleware is NOT appended globally here because the
        // auth endpoints (login, refresh) receive the tenant_id in the request
        // body rather than via JWT claims or headers. Protected routes that do
        // need the tenant context (me, logout, revoke) use the AuthContext which
        // is hydrated from JWT claims by VerifyJwtMiddleware.
        $middleware->alias([
            'jwt.verify'     => VerifyJwtMiddleware::class,
            'auth.ratelimit' => RateLimitAuthMiddleware::class,
            'tenant'         => TenantContextMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            \KvEnterprise\SharedKernel\Exceptions\AuthorizationException $e,
        ) {
            return \KvEnterprise\SharedKernel\Http\Responses\ApiResponse::forbidden($e->getMessage());
        });

        $exceptions->render(function (
            \KvEnterprise\SharedKernel\Exceptions\NotFoundException $e,
        ) {
            return \KvEnterprise\SharedKernel\Http\Responses\ApiResponse::notFound($e->getMessage());
        });

        $exceptions->render(function (
            \Illuminate\Validation\ValidationException $e,
        ) {
            return \KvEnterprise\SharedKernel\Http\Responses\ApiResponse::validationError(
                $e->errors(),
                $e->getMessage(),
            );
        });
    })->create();
