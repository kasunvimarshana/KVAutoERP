<?php

namespace App\DTOs;

class SagaStepDTO
{
    public function __construct(
        public readonly string  $name,
        public readonly string  $status,
        public readonly ?string $executedAt,
        public readonly ?string $compensatedAt,
        public readonly ?string $error,
    ) {}

    public static function fromArray(array $step): self
    {
        return new self(
            name:          $step['name'],
            status:        $step['status'],
            executedAt:    $step['executed_at'] ?? null,
            compensatedAt: $step['compensated_at'] ?? null,
            error:         $step['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name'           => $this->name,
            'status'         => $this->status,
            'executed_at'    => $this->executedAt,
            'compensated_at' => $this->compensatedAt,
            'error'          => $this->error,
        ];
    }
}
