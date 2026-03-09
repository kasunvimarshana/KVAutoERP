<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [];

    /**
     * A list of the exception types that are not reported.
     *
     * @var list<class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var list<string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e): void {
            // Custom reporting (e.g. Sentry, Bugsnag) can be added here
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): mixed
    {
        // Force JSON responses for API requests
        if ($request->expectsJson() || $request->is('api/*') || $request->is('health*')) {
            return $this->renderApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    private function renderApiException(Request $request, Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($e instanceof HttpException) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'HTTP error.',
            ], $e->getStatusCode());
        }

        $status  = method_exists($e, 'getCode') && $e->getCode() >= 400 && $e->getCode() < 600
            ? (int) $e->getCode()
            : 500;

        $message = config('app.debug')
            ? $e->getMessage()
            : 'An unexpected error occurred. Please try again later.';

        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
