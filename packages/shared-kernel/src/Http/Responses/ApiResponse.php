<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Http\Responses;

use Illuminate\Http\JsonResponse;
use KvEnterprise\SharedKernel\DTOs\PaginationDTO;

/**
 * Static helper for building consistent JSON API response envelopes.
 *
 * Every response follows the structure:
 * {
 *   "status":  "success" | "error",
 *   "message": "…",
 *   "data":    <payload | null>,
 *   "meta":    { … },
 *   "errors":  { … }
 * }
 *
 * All microservices MUST use this class for their HTTP responses to
 * guarantee a uniform contract with API consumers.
 */
final class ApiResponse
{
    /**
     * Return a successful JSON response.
     *
     * @param  mixed                  $data        Response payload (scalar, array, or Resource).
     * @param  string                 $message     Human-readable success message.
     * @param  array<string, mixed>   $meta        Additional metadata (e.g. timing, version).
     * @param  int                    $statusCode  HTTP status code (default 200).
     * @return JsonResponse
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        array $meta = [],
        int $statusCode = 200,
    ): JsonResponse {
        return new JsonResponse(
            [
                'status'  => 'success',
                'message' => $message,
                'data'    => $data,
                'meta'    => $meta,
                'errors'  => [],
            ],
            $statusCode,
        );
    }

    /**
     * Return an error JSON response.
     *
     * @param  string                 $message     Human-readable error summary.
     * @param  array<string, mixed>   $errors      Field-level validation errors or structured details.
     * @param  int                    $statusCode  HTTP status code (default 400).
     * @return JsonResponse
     */
    public static function error(
        string $message = 'An error occurred',
        array $errors = [],
        int $statusCode = 400,
    ): JsonResponse {
        return new JsonResponse(
            [
                'status'  => 'error',
                'message' => $message,
                'data'    => null,
                'meta'    => [],
                'errors'  => $errors,
            ],
            $statusCode,
        );
    }

    /**
     * Return a paginated success JSON response.
     *
     * Items are placed under `data`; pagination metadata is nested under
     * `meta.pagination` following the platform's API contract.
     *
     * @param  mixed          $data        The page items (array or Resource collection).
     * @param  PaginationDTO  $pagination  Pagination metadata object.
     * @param  string         $message     Human-readable message (default "Success").
     * @return JsonResponse
     */
    public static function paginated(
        mixed $data,
        PaginationDTO $pagination,
        string $message = 'Success',
    ): JsonResponse {
        return new JsonResponse(
            [
                'status'  => 'success',
                'message' => $message,
                'data'    => $data,
                'meta'    => ['pagination' => $pagination->toArray()],
                'errors'  => [],
            ],
            200,
        );
    }

    /**
     * Return a 201 Created JSON response.
     *
     * @param  mixed   $data     The newly created resource.
     * @param  string  $message  Human-readable message (default "Resource created successfully.").
     * @return JsonResponse
     */
    public static function created(
        mixed $data = null,
        string $message = 'Resource created successfully.',
    ): JsonResponse {
        return new JsonResponse(
            [
                'status'  => 'success',
                'message' => $message,
                'data'    => $data,
                'meta'    => [],
                'errors'  => [],
            ],
            201,
        );
    }

    /**
     * Return a 204 No Content response.
     *
     * The response body is empty as per the HTTP specification for 204.
     *
     * @return JsonResponse
     */
    public static function noContent(): JsonResponse
    {
        return new JsonResponse(null, 204);
    }

    /**
     * Return a 422 Unprocessable Entity response for validation failures.
     *
     * @param  array<string, string|array<int, string>>  $errors   Field-keyed validation messages.
     * @param  string                                     $message  Human-readable summary.
     * @return JsonResponse
     */
    public static function validationError(
        array $errors,
        string $message = 'The given data was invalid.',
    ): JsonResponse {
        return self::error($message, $errors, 422);
    }

    /**
     * Return a 401 Unauthorised response.
     *
     * @param  string  $message  Human-readable message.
     * @return JsonResponse
     */
    public static function unauthorized(string $message = 'Unauthenticated.'): JsonResponse
    {
        return self::error($message, [], 401);
    }

    /**
     * Return a 403 Forbidden response.
     *
     * @param  string  $message  Human-readable message.
     * @return JsonResponse
     */
    public static function forbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return self::error($message, [], 403);
    }

    /**
     * Return a 404 Not Found response.
     *
     * @param  string  $message  Human-readable message.
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return self::error($message, [], 404);
    }

    /**
     * Return a 500 Internal Server Error response.
     *
     * @param  string  $message  Human-readable message (must not expose internal details in production).
     * @return JsonResponse
     */
    public static function serverError(string $message = 'An internal server error occurred.'): JsonResponse
    {
        return self::error($message, [], 500);
    }
}
