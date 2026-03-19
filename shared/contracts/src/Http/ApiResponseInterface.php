<?php

declare(strict_types=1);

namespace KvSaas\Contracts\Http;

/**
 * Standardised API response envelope used by every microservice.
 *
 * All responses follow the shape:
 * { "success": bool, "data": mixed, "meta": {}, "errors": null|array, "message": string }
 */
interface ApiResponseInterface
{
    /** @return array<string, mixed> */
    public function success(mixed $data, string $message = 'OK', array $meta = []): array;

    /** @return array<string, mixed> */
    public function error(string $message, array $errors = [], int $code = 400): array;

    /** @return array<string, mixed> */
    public function paginated(mixed $data, array $pagination, string $message = 'OK'): array;
}
