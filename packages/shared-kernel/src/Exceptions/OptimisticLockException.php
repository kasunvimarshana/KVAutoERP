<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Exceptions;

/**
 * Thrown when an optimistic locking conflict is detected during a model save.
 *
 * This indicates another process has already incremented the `version` column
 * since the current in-memory model was loaded. Callers should reload the
 * model, re-apply their changes, and retry the save.
 *
 * Maps to HTTP 409 Conflict in API response handlers.
 */
class OptimisticLockException extends DomainException
{
    /** HTTP status code for optimistic lock conflicts. */
    public const HTTP_STATUS = 409;

    /**
     * @param  string                 $message   Human-readable conflict description.
     * @param  array<string, mixed>   $context   Diagnostic context: model, id, expected version.
     * @param  \Throwable|null        $previous  The originating exception, if any.
     */
    public function __construct(
        string $message = 'A concurrent modification conflict was detected. Please reload and retry.',
        array $context = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $context, self::HTTP_STATUS, $previous);
    }

    /**
     * Named constructor for a version conflict on a specific model.
     *
     * @param  string      $modelClass      FQCN of the model class.
     * @param  string|int  $id              Primary key of the affected record.
     * @param  int         $expectedVersion The version the caller expected.
     * @return self
     */
    public static function forModel(string $modelClass, string|int $id, int $expectedVersion): self
    {
        return new self(
            sprintf(
                'Optimistic lock conflict on %s #%s: expected version %d but the record was already modified.',
                $modelClass,
                $id,
                $expectedVersion,
            ),
            [
                'model'            => $modelClass,
                'id'               => $id,
                'expected_version' => $expectedVersion,
            ],
        );
    }
}
