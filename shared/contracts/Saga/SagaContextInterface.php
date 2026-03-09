<?php

declare(strict_types=1);

namespace Shared\Contracts\Saga;

/**
 * Saga Context Interface.
 *
 * Carries shared state between saga steps.
 */
interface SagaContextInterface
{
    /**
     * Get a value from the context.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a value in the context.
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function set(string $key, mixed $value): void;

    /**
     * Check if a key exists in the context.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Get all context data as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Get the saga transaction identifier.
     *
     * @return string
     */
    public function getTransactionId(): string;

    /**
     * Mark the context as failed with an error message.
     *
     * @param string     $stepName The step that failed
     * @param \Throwable $error
     * @return void
     */
    public function markFailed(string $stepName, \Throwable $error): void;

    /**
     * Check if the saga has been marked as failed.
     *
     * @return bool
     */
    public function hasFailed(): bool;
}
