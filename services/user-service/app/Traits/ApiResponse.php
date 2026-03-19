<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(
        mixed  $data       = null,
        string $message    = 'Operation successful',
        int    $statusCode = 200,
        array  $meta       = [],
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => $meta,
            'errors'  => null,
            'message' => $message,
        ], $statusCode);
    }

    protected function errorResponse(
        string $message    = 'An error occurred',
        array  $errors     = [],
        int    $statusCode = 400,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'data'    => null,
            'meta'    => [],
            'errors'  => $errors,
            'message' => $message,
        ], $statusCode);
    }

    protected function paginatedResponse(
        mixed  $data,
        array  $pagination,
        string $message = 'OK',
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => ['pagination' => $pagination],
            'errors'  => null,
            'message' => $message,
        ]);
    }
}
