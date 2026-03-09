<?php

declare(strict_types=1);

namespace Saas\Contracts\Http;

/**
 * Contract for a standardised JSON API response factory.
 *
 * All microservice HTTP controllers MUST use an implementation of this interface
 * to ensure a consistent response envelope across the platform.
 *
 * Canonical envelope shapes:
 *
 * Success:
 * ```json
 * { "success": true, "message": "...", "data": { ... }, "statusCode": 200 }
 * ```
 *
 * Error:
 * ```json
 * { "success": false, "message": "...", "errors": { ... }, "statusCode": 400 }
 * ```
 *
 * Paginated:
 * ```json
 * { "success": true, "message": "...", "data": [...], "meta": { "total": 100, ... } }
 * ```
 */
interface ApiResponseInterface
{
    /**
     * Builds a successful response.
     *
     * @param mixed  $data       Response payload (scalar, array, or object).
     * @param string $message    Human-readable success message.
     * @param int    $statusCode HTTP status code (default: 200).
     *
     * @return mixed An HTTP response object (framework-specific) or an array.
     */
    public function success(mixed $data, string $message = '', int $statusCode = 200): mixed;

    /**
     * Builds an error response.
     *
     * @param string               $message    Human-readable error summary.
     * @param array<string, mixed> $errors     Field-level or structured validation/domain errors.
     * @param int                  $statusCode HTTP status code (default: 400).
     *
     * @return mixed An HTTP response object (framework-specific) or an array.
     */
    public function error(string $message, array $errors = [], int $statusCode = 400): mixed;

    /**
     * Builds a paginated collection response.
     *
     * @param mixed                $data    The current page of records.
     * @param array<string, mixed> $meta    Pagination metadata, e.g.:
     *                                      `total`, `perPage`, `currentPage`, `lastPage`, `from`, `to`.
     * @param string               $message Optional human-readable message.
     *
     * @return mixed An HTTP response object (framework-specific) or an array.
     */
    public function paginated(mixed $data, array $meta, string $message = ''): mixed;
}
