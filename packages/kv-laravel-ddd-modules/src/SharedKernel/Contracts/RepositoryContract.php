<?php

declare(strict_types=1);

namespace LaravelDDD\SharedKernel\Contracts;

/**
 * Generic repository contract.
 *
 * Repositories abstract persistence of domain entities.
 */
interface RepositoryContract
{
    /**
     * Find an entity by its identifier.
     *
     * @param  mixed  $id
     * @return object|null
     */
    public function findById(mixed $id): ?object;

    /**
     * Persist a domain entity (insert or update).
     *
     * @param  object  $entity
     * @return void
     */
    public function save(object $entity): void;

    /**
     * Remove an entity by its identifier.
     *
     * @param  mixed  $id
     * @return void
     */
    public function delete(mixed $id): void;
}
