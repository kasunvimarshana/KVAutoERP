<?php

declare(strict_types=1);

use App\Http\Middleware\Authenticate;
use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        AppServiceProvider::class,
        AuthServiceProvider::class,
    ])
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register named middleware aliases.
        $middleware->alias([
            'auth'   => Authenticate::class,
            'tenant' => \App\Shared\Tenant\TenantMiddleware::class,
            'rbac'   => \App\Shared\Auth\RbacMiddleware::class,
            'abac'   => \App\Shared\Auth\AbacMiddleware::class,
        ]);

        // Apply to all API routes.
        $middleware->appendToGroup('api', [
            \App\Shared\Tenant\TenantMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON for all API exceptions.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null;
            }

            $envelope = [
                'success' => false,
                'data'    => null,
                'meta'    => ['request_id' => $request->header('X-Request-ID')],
                'errors'  => [],
            ];

            if ($e instanceof ValidationException) {
                return response()->json(
                    array_merge($envelope, [
                        'message' => 'Validation failed',
                        'errors'  => $e->errors(),
                    ]),
                    422,
                );
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json(
                    array_merge($envelope, ['message' => 'Unauthenticated.']),
                    401,
                );
            }

            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json(
                    array_merge($envelope, ['message' => 'Forbidden.']),
                    403,
                );
            }

            if ($e instanceof NotFoundHttpException
                || $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
            ) {
                return response()->json(
                    array_merge($envelope, ['message' => 'Resource not found.']),
                    404,
                );
            }

            $status  = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = app()->environment('production')
                ? 'An unexpected error occurred.'
                : $e->getMessage();

            return response()->json(
                array_merge($envelope, ['message' => $message]),
                $status >= 100 && $status < 600 ? $status : 500,
            );
        });
    })
    ->create();
