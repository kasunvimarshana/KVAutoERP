<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class TransactionRule
{
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private readonly string $name,
        private readonly array $conditions,
        private readonly ?string $categoryId,
        private readonly ?string $accountId,
        private readonly string $applyTo,
        private readonly int $priority,
        private readonly bool $isActive,
    ) {}

    public function getId(): string { return $this->id; }
    public function getTenantId(): string { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getConditions(): array { return $this->conditions; }
    public function getCategoryId(): ?string { return $this->categoryId; }
    public function getAccountId(): ?string { return $this->accountId; }
    public function getApplyTo(): string { return $this->applyTo; }
    public function getPriority(): int { return $this->priority; }
    public function isActive(): bool { return $this->isActive; }

    public function matches(BankTransaction $transaction): bool
    {
        if (! $this->isActive) {
            return false;
        }

        if ($this->applyTo !== 'all' && $this->applyTo !== $transaction->getType()) {
            return false;
        }

        foreach ($this->conditions as $condition) {
            if (! $this->evaluateCondition($condition, $transaction)) {
                return false;
            }
        }

        return true;
    }

    private function evaluateCondition(array $condition, BankTransaction $transaction): bool
    {
        $field    = $condition['field'] ?? '';
        $operator = $condition['operator'] ?? '=';
        $value    = $condition['value'] ?? '';

        $actual = match ($field) {
            'description' => $transaction->getDescription(),
            'amount'      => (string) $transaction->getAmount(),
            'type'        => $transaction->getType(),
            default       => '',
        };

        return match ($operator) {
            'contains'     => str_contains(strtolower((string) $actual), strtolower((string) $value)),
            'starts_with'  => str_starts_with(strtolower((string) $actual), strtolower((string) $value)),
            'ends_with'    => str_ends_with(strtolower((string) $actual), strtolower((string) $value)),
            '>'            => (float) $actual > (float) $value,
            '>='           => (float) $actual >= (float) $value,
            '<'            => (float) $actual < (float) $value,
            '<='           => (float) $actual <= (float) $value,
            default        => strtolower((string) $actual) === strtolower((string) $value),
        };
    }
}
