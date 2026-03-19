<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyJwtMiddleware;
use App\Http\Middleware\VerifyServiceKeyMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.verify'   => VerifyJwtMiddleware::class,
            'service.auth' => VerifyServiceKeyMiddleware::class,
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
