<?php

declare(strict_types=1);

namespace App\Support\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Provides standardized JSON response helpers for all API controllers.
 */
trait ApiResponse
{
    protected function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200,
        array $headers = [],
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status, $headers);
    }

    protected function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function error(
        string $message = 'An error occurred',
        int $status = 400,
        mixed $errors = null,
        array $headers = [],
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status, $headers);
    }

    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    protected function notFound(string $message = 'Not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function unprocessable(mixed $errors = null, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    protected function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return $this->error($message, 500);
    }

    protected function paginated(
        LengthAwarePaginator $paginator,
        string $message = 'Success',
    ): JsonResponse {
        return response()->json([
            'success'  => true,
            'message'  => $message,
            'data'     => $paginator->items(),
            'meta'     => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
        ]);
    }
}
