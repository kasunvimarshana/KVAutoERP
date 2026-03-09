<?php

use App\Http\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.jwt' => Authenticate::class,
            'tenant'   => \App\Shared\Tenant\TenantMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            return response()->json([
                'error'   => 'Validation failed.',
                'details' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (\RuntimeException $e, $request) {
            $status = str_contains($e->getMessage(), 'not found') ? 404 : 400;
            return response()->json(['error' => $e->getMessage()], $status);
        });
    })
    ->create();
