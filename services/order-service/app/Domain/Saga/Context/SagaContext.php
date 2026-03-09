<?php

declare(strict_types=1);

namespace App\Domain\Saga\Context;

/**
 * Saga Context.
 *
 * Mutable shared state passed between saga steps.
 * Carries transaction data, intermediate results, and failure info.
 */
class SagaContext
{
    /** @var array<string, mixed> */
    private array $data;

    private bool $failed = false;

    private ?string $failedStep = null;

    private ?\Throwable $error = null;

    /**
     * @param string               $transactionId
     * @param array<string, mixed> $initialData
     */
    public function __construct(
        private readonly string $transactionId,
        array $initialData = [],
    ) {
        $this->data = $initialData;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        data_set($this->data, $key, $value);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->data;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function markFailed(string $stepName, \Throwable $error): void
    {
        $this->failed      = true;
        $this->failedStep  = $stepName;
        $this->error       = $error;
    }

    public function hasFailed(): bool
    {
        return $this->failed;
    }

    public function getError(): ?\Throwable
    {
        return $this->error;
    }

    public function getFailedStep(): ?string
    {
        return $this->failedStep;
    }
}
