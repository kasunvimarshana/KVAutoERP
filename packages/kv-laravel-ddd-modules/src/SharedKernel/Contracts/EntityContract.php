<?php

declare(strict_types=1);

namespace LaravelDDD\SharedKernel\Contracts;

/**
 * Contract for all domain entities.
 */
interface EntityContract
{
    /**
     * Return the entity's unique identifier.
     *
     * @return mixed
     */
    public function getId(): mixed;

    /**
     * Determine whether two entities are equal (same identity).
     *
     * @param  EntityContract  $other
     * @return bool
     */
    public function equals(EntityContract $other): bool;
}
