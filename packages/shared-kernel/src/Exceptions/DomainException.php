<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Exceptions;

use RuntimeException;

/**
 * Base exception for all domain-level errors.
 *
 * Carries an optional context array that provides structured diagnostic
 * data for logging and monitoring without leaking implementation details
 * to API consumers.
 */
class DomainException extends RuntimeException
{
    /**
     * @param  string                 $message   Human-readable error description.
     * @param  array<string, mixed>   $context   Structured diagnostic context (never exposed in API responses).
     * @param  int                    $code      Optional application-level error code.
     * @param  \Throwable|null        $previous  The originating exception, if any.
     */
    public function __construct(
        string $message = '',
        private readonly array $context = [],
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Return the structured diagnostic context array.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Return a loggable representation of this exception.
     *
     * @return array<string, mixed>
     */
    public function toLogArray(): array
    {
        return [
            'exception' => static::class,
            'message'   => $this->getMessage(),
            'code'      => $this->getCode(),
            'context'   => $this->context,
            'file'      => $this->getFile(),
            'line'      => $this->getLine(),
        ];
    }
}
