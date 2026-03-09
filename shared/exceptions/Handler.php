<?php

declare(strict_types=1);

namespace App\Shared\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Global Exception Handler.
 *
 * Converts all exceptions into the standard KV_SAAS JSON envelope:
 *
 * {
 *   "success": false,
 *   "message": "...",
 *   "data":    null,
 *   "meta":    { "request_id": "...", "exception": "..." },
 *   "errors":  []
 * }
 *
 * Extend Laravel's built-in handler and delegate the render method here:
 *
 *   // app/Exceptions/Handler.php
 *   public function render($request, Throwable $e): Response
 *   {
 *       if ($request->expectsJson()) {
 *           return (new \App\Shared\Exceptions\Handler())->render($request, $e);
 *       }
 *       return parent::render($request, $e);
 *   }
 */
final class Handler
{
    /**
     * Convert a Throwable into a consistent JSON error response.
     *
     * @param  Request    $request
     * @param  Throwable  $e
     * @return JsonResponse
     */
    public function render(Request $request, Throwable $e): JsonResponse
    {
        return match (true) {
            $e instanceof ValidationException     => $this->handleValidation($request, $e),
            $e instanceof AuthenticationException => $this->handleAuthentication($request, $e),
            $e instanceof AuthorizationException  => $this->handleAuthorization($request, $e),
            $e instanceof ModelNotFoundException  => $this->handleModelNotFound($request, $e),
            $e instanceof HttpException           => $this->handleHttpException($request, $e),
            default                               => $this->handleGeneric($request, $e),
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Per-exception handlers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Handle 422 Unprocessable Entity (validation failures).
     *
     * @param  Request              $request
     * @param  ValidationException  $e
     * @return JsonResponse
     */
    private function handleValidation(Request $request, ValidationException $e): JsonResponse
    {
        return $this->jsonResponse(
            status: 422,
            message: $e->getMessage(),
            errors: $e->errors(),
            request: $request,
        );
    }

    /**
     * Handle 401 Unauthorized (unauthenticated requests).
     *
     * @param  Request                 $request
     * @param  AuthenticationException $e
     * @return JsonResponse
     */
    private function handleAuthentication(Request $request, AuthenticationException $e): JsonResponse
    {
        return $this->jsonResponse(
            status: 401,
            message: 'Unauthenticated. Please log in to continue.',
            request: $request,
        );
    }

    /**
     * Handle 403 Forbidden (insufficient permissions).
     *
     * @param  Request               $request
     * @param  AuthorizationException $e
     * @return JsonResponse
     */
    private function handleAuthorization(Request $request, AuthorizationException $e): JsonResponse
    {
        return $this->jsonResponse(
            status: 403,
            message: $e->getMessage() ?: 'You are not authorized to perform this action.',
            request: $request,
        );
    }

    /**
     * Handle 404 Not Found (Eloquent model not found).
     *
     * @param  Request                 $request
     * @param  ModelNotFoundException  $e
     * @return JsonResponse
     */
    private function handleModelNotFound(Request $request, ModelNotFoundException $e): JsonResponse
    {
        $model = class_basename($e->getModel());

        return $this->jsonResponse(
            status: 404,
            message: "{$model} not found.",
            request: $request,
        );
    }

    /**
     * Handle generic HTTP exceptions (HttpException with a status code).
     *
     * @param  Request       $request
     * @param  HttpException $e
     * @return JsonResponse
     */
    private function handleHttpException(Request $request, HttpException $e): JsonResponse
    {
        $status  = $e->getStatusCode();
        $message = $e->getMessage() ?: $this->defaultMessageForStatus($status);

        return $this->jsonResponse(
            status: $status,
            message: $message,
            request: $request,
        );
    }

    /**
     * Handle all other (unexpected) exceptions as 500 Internal Server Error.
     *
     * In production, the exception details are hidden to avoid information leakage.
     *
     * @param  Request    $request
     * @param  Throwable  $e
     * @return JsonResponse
     */
    private function handleGeneric(Request $request, Throwable $e): JsonResponse
    {
        $isDebug = config('app.debug', false);

        $meta = $this->baseMeta($request);

        if ($isDebug) {
            $meta['exception'] = get_class($e);
            $meta['trace']     = collect(explode("\n", $e->getTraceAsString()))
                ->take(15)
                ->values()
                ->toArray();
        }

        return $this->jsonResponse(
            status: 500,
            message: $isDebug
                ? $e->getMessage()
                : 'An unexpected error occurred. Please try again later.',
            request: $request,
            extraMeta: $meta,
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build the standardised JSON response envelope.
     *
     * @param  int                     $status
     * @param  string                  $message
     * @param  array<string,mixed>     $errors
     * @param  Request                 $request
     * @param  array<string,mixed>     $extraMeta
     * @return JsonResponse
     */
    private function jsonResponse(
        int $status,
        string $message,
        array $errors = [],
        Request $request = new Request(),
        array $extraMeta = [],
    ): JsonResponse {
        $meta = array_merge($this->baseMeta($request), $extraMeta);

        return response()->json(
            data: [
                'success' => false,
                'message' => $message,
                'data'    => null,
                'meta'    => $meta,
                'errors'  => $errors,
            ],
            status: $status,
            headers: [
                'X-Request-ID' => $request->header('X-Request-ID', ''),
            ],
        );
    }

    /**
     * Build the base meta array from the current request.
     *
     * @param  Request  $request
     * @return array<string,mixed>
     */
    private function baseMeta(Request $request): array
    {
        return [
            'request_id' => $request->header('X-Request-ID'),
            'timestamp'  => now()->toIso8601String(),
        ];
    }

    /**
     * Return a human-readable message for common HTTP status codes.
     *
     * @param  int  $status
     * @return string
     */
    private function defaultMessageForStatus(int $status): string
    {
        return match ($status) {
            400 => 'Bad request.',
            401 => 'Unauthorized.',
            403 => 'Forbidden.',
            404 => 'Resource not found.',
            405 => 'Method not allowed.',
            409 => 'Conflict.',
            410 => 'Gone.',
            429 => 'Too many requests.',
            500 => 'Internal server error.',
            502 => 'Bad gateway.',
            503 => 'Service unavailable.',
            default => 'An error occurred.',
        };
    }
}
