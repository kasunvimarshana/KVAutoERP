<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\DTOs;

/**
 * Data Transfer Object for standardised API response envelopes.
 *
 * Every API response across the platform must conform to this structure:
 *
 * {
 *   "status":  "success" | "error",
 *   "message": "Human-readable summary",
 *   "data":    <payload | null>,
 *   "meta":    { ...pagination, timing, … },
 *   "errors":  { ...field-level validation errors }
 * }
 */
final class ApiResponseDTO
{
    /**
     * @param  mixed                            $data     Response payload.
     * @param  array<string, mixed>             $meta     Metadata (pagination, timing, …).
     * @param  array<string, mixed>             $errors   Field-level error details.
     * @param  int                              $status   HTTP status code.
     * @param  string                           $message  Human-readable summary.
     * @param  'success'|'error'                $type     Response type discriminator.
     */
    public function __construct(
        public readonly mixed $data,
        public readonly array $meta,
        public readonly array $errors,
        public readonly int $status,
        public readonly string $message,
        public readonly string $type,
    ) {}

    /**
     * Build a successful response DTO.
     *
     * @param  mixed                  $data       Response payload.
     * @param  string                 $message    Human-readable success message.
     * @param  array<string, mixed>   $meta       Additional metadata.
     * @param  int                    $statusCode HTTP status code (default 200).
     * @return self
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        array $meta = [],
        int $statusCode = 200,
    ): self {
        return new self(
            data:    $data,
            meta:    $meta,
            errors:  [],
            status:  $statusCode,
            message: $message,
            type:    'success',
        );
    }

    /**
     * Build an error response DTO.
     *
     * @param  string                 $message    Human-readable error summary.
     * @param  array<string, mixed>   $errors     Field-level validation errors or details.
     * @param  int                    $statusCode HTTP status code (default 400).
     * @return self
     */
    public static function error(
        string $message = 'An error occurred',
        array $errors = [],
        int $statusCode = 400,
    ): self {
        return new self(
            data:    null,
            meta:    [],
            errors:  $errors,
            status:  $statusCode,
            message: $message,
            type:    'error',
        );
    }

    /**
     * Build a paginated success response DTO.
     *
     * @param  mixed                  $data        The page items.
     * @param  PaginationDTO          $pagination  Pagination metadata.
     * @param  string                 $message     Human-readable message.
     * @return self
     */
    public static function paginated(
        mixed $data,
        PaginationDTO $pagination,
        string $message = 'Success',
    ): self {
        return new self(
            data:    $data,
            meta:    ['pagination' => $pagination->toArray()],
            errors:  [],
            status:  200,
            message: $message,
            type:    'success',
        );
    }

    /**
     * Determine whether this is a successful response.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->type === 'success';
    }

    /**
     * Determine whether this is an error response.
     *
     * @return bool
     */
    public function isError(): bool
    {
        return $this->type === 'error';
    }

    /**
     * Serialise the DTO to the standard API response envelope array.
     *
     * @return array{status: string, message: string, data: mixed, meta: array<string, mixed>, errors: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'status'  => $this->type,
            'message' => $this->message,
            'data'    => $this->data,
            'meta'    => $this->meta,
            'errors'  => $this->errors,
        ];
    }
}
