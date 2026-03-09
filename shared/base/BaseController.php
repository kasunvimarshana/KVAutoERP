<?php

declare(strict_types=1);

namespace App\Shared\Base;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Abstract Base Controller.
 *
 * Centralises the JSON response format for every micro-service in KV_SAAS.
 *
 * All responses share the following envelope:
 *
 * {
 *   "success": true|false,
 *   "message": "Human-readable description",
 *   "data":    <mixed>,
 *   "meta":    { "pagination": { ... } | null },
 *   "errors":  [ ... ]
 * }
 */
abstract class BaseController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Response builders
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return a generic success response.
     *
     * @param  mixed   $data     Response payload.
     * @param  string  $message  Human-readable message.
     * @param  int     $status   HTTP status code (default 200).
     * @return JsonResponse
     */
    protected function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200,
    ): JsonResponse {
        return response()->json(
            data: $this->envelope(
                success: true,
                message: $message,
                data: $data,
            ),
            status: $status,
        );
    }

    /**
     * Return a 201 Created response.
     *
     * @param  mixed   $data
     * @param  string  $message
     * @return JsonResponse
     */
    protected function created(
        mixed $data = null,
        string $message = 'Resource created successfully',
    ): JsonResponse {
        return $this->success($data, $message, 201);
    }

    /**
     * Return a 204 No Content response.
     *
     * @return JsonResponse
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return a paginated success response.
     *
     * The pagination metadata is extracted from the paginator and placed in
     * the `meta.pagination` key for API consumers.
     *
     * @param  LengthAwarePaginator  $paginator
     * @param  string                $message
     * @return JsonResponse
     */
    protected function paginated(
        LengthAwarePaginator $paginator,
        string $message = 'Success',
    ): JsonResponse {
        $envelope = $this->envelope(
            success: true,
            message: $message,
            data: $paginator->items(),
            meta: [
                'pagination' => [
                    'total'        => $paginator->total(),
                    'per_page'     => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'from'         => $paginator->firstItem(),
                    'to'           => $paginator->lastItem(),
                    'has_more'     => $paginator->hasMorePages(),
                    'next_page_url'  => $paginator->nextPageUrl(),
                    'prev_page_url'  => $paginator->previousPageUrl(),
                ],
            ],
        );

        return response()->json($envelope, 200);
    }

    /**
     * Return an error response.
     *
     * @param  string        $message  Human-readable error summary.
     * @param  int           $status   HTTP error status (4xx/5xx).
     * @param  array<mixed>  $errors   Detailed error list (e.g. validation messages).
     * @return JsonResponse
     */
    protected function error(
        string $message,
        int $status = 400,
        array $errors = [],
    ): JsonResponse {
        return response()->json(
            data: $this->envelope(
                success: false,
                message: $message,
                data: null,
                errors: $errors,
            ),
            status: $status,
        );
    }

    /**
     * Return a 422 Unprocessable Entity response with validation errors.
     *
     * @param  array<string, array<string>>  $errors  Field → messages map.
     * @param  string                        $message
     * @return JsonResponse
     */
    protected function validationError(
        array $errors,
        string $message = 'Validation failed',
    ): JsonResponse {
        return $this->error($message, 422, $errors);
    }

    /**
     * Return a 404 Not Found response.
     *
     * @param  string  $message
     * @return JsonResponse
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Return a 403 Forbidden response.
     *
     * @param  string  $message
     * @return JsonResponse
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * Return a 401 Unauthorized response.
     *
     * @param  string  $message
     * @return JsonResponse
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build the standard JSON envelope array.
     *
     * @param  bool             $success
     * @param  string           $message
     * @param  mixed            $data
     * @param  array            $meta
     * @param  array            $errors
     * @return array<string, mixed>
     */
    private function envelope(
        bool $success,
        string $message,
        mixed $data,
        array $meta = [],
        array $errors = [],
    ): array {
        return [
            'success' => $success,
            'message' => $message,
            'data'    => $data,
            'meta'    => array_merge(
                ['pagination' => null, 'request_id' => $this->requestId()],
                $meta,
            ),
            'errors'  => $errors,
        ];
    }

    /**
     * Extract the X-Request-ID from the current request for response correlation.
     *
     * @return string|null
     */
    private function requestId(): ?string
    {
        return request()->header('X-Request-ID');
    }
}
