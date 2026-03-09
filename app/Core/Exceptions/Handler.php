<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Global exception handler with JSON-first API responses.
 */
class Handler extends ExceptionHandler
{
    /** @var array<int,class-string<Throwable>> */
    protected $dontReport = [];

    /** @var array<int,string> */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register application exception handling callbacks.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e): void {
            // Report to external monitoring (Sentry, Bugsnag, etc.) here
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * Forces JSON for all API routes (Accept: application/json or /api/ prefix).
     */
    public function render($request, Throwable $e): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->renderApiException($e);
        }

        return parent::render($request, $e);
    }

    /**
     * Convert any exception to a consistent JSON error envelope.
     */
    private function renderApiException(Throwable $e): \Illuminate\Http\JsonResponse
    {
        [$statusCode, $message, $errors] = match (true) {
            $e instanceof ValidationException      => [422, 'Validation failed.', $e->errors()],
            $e instanceof AuthenticationException  => [401, 'Unauthenticated.', []],
            $e instanceof AuthorizationException   => [403, 'Forbidden.', []],
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException    => [404, 'Resource not found.', []],
            $e instanceof HttpException            => [$e->getStatusCode(), $e->getMessage(), []],
            default                                => [500, config('app.debug')
                ? $e->getMessage()
                : 'An unexpected error occurred.', []],
        };

        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        if (config('app.debug') && $statusCode === 500) {
            $payload['debug'] = [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => collect($e->getTrace())->take(10)->toArray(),
            ];
        }

        return response()->json($payload, $statusCode);
    }
}
