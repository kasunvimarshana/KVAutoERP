<?php declare(strict_types=1);
namespace Modules\Accounting\Domain\Entities;
class TransactionRule {
    public function __construct(
        private readonly ?int $id,
        private readonly int $tenantId,
        private readonly string $name,
        private readonly string $applyTo,  // all|debit|credit
        private readonly string $matchField, // description|reference
        private readonly string $matchValue,
        private readonly int $categoryAccountId,
        private readonly int $priority,
    ) {}
    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getName(): string { return $this->name; }
    public function getApplyTo(): string { return $this->applyTo; }
    public function getMatchField(): string { return $this->matchField; }
    public function getMatchValue(): string { return $this->matchValue; }
    public function getCategoryAccountId(): int { return $this->categoryAccountId; }
    public function getPriority(): int { return $this->priority; }
    public function matches(BankTransaction $txn): bool {
        if ($this->applyTo !== 'all' && $this->applyTo !== $txn->getType()) return false;
        $haystack = $this->matchField === 'description' ? $txn->getDescription() : ($txn->getReference() ?? '');
        return stripos($haystack, $this->matchValue) !== false;
    }
}
