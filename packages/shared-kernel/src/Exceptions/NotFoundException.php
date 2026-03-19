<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Exceptions;

/**
 * Thrown when a requested resource cannot be found.
 *
 * Maps to HTTP 404 Not Found in API response handlers.
 */
class NotFoundException extends DomainException
{
    /** Default HTTP status code for this exception type. */
    public const HTTP_STATUS = 404;

    /**
     * @param  string                 $resource   Human-readable resource type (e.g. "Order", "Product").
     * @param  string|int             $id         The identifier that was looked up.
     * @param  array<string, mixed>   $context    Additional diagnostic context.
     * @param  \Throwable|null        $previous   The originating exception, if any.
     */
    public function __construct(
        private readonly string $resource,
        private readonly string|int $id,
        array $context = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            message:  sprintf('%s with identifier "%s" was not found.', $resource, $id),
            context:  array_merge(['resource' => $resource, 'id' => $id], $context),
            code:     self::HTTP_STATUS,
            previous: $previous,
        );
    }

    /**
     * Named constructor for a cleaner call-site syntax.
     *
     * @param  string      $resource  Human-readable resource type.
     * @param  string|int  $id        The identifier that was looked up.
     * @return self
     */
    public static function for(string $resource, string|int $id): self
    {
        return new self($resource, $id);
    }

    /**
     * Return the human-readable resource type name.
     *
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * Return the identifier that was looked up.
     *
     * @return string|int
     */
    public function getId(): string|int
    {
        return $this->id;
    }
}
