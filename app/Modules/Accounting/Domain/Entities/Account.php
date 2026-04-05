<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class Account
{
    public const TYPE_ASSET           = 'asset';
    public const TYPE_LIABILITY       = 'liability';
    public const TYPE_EQUITY          = 'equity';
    public const TYPE_INCOME          = 'income';
    public const TYPE_EXPENSE         = 'expense';
    public const TYPE_BANK            = 'bank';
    public const TYPE_CREDIT_CARD     = 'credit_card';
    public const TYPE_AP              = 'accounts_payable';
    public const TYPE_AR              = 'accounts_receivable';

    public const TYPES = [
        self::TYPE_ASSET,
        self::TYPE_LIABILITY,
        self::TYPE_EQUITY,
        self::TYPE_INCOME,
        self::TYPE_EXPENSE,
        self::TYPE_BANK,
        self::TYPE_CREDIT_CARD,
        self::TYPE_AP,
        self::TYPE_AR,
    ];

    public const NORMAL_DEBIT  = 'debit';
    public const NORMAL_CREDIT = 'credit';

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly ?int $parentId,
        public readonly string $code,
        public readonly string $name,
        public readonly string $type,
        public readonly string $normalBalance,
        public readonly bool $isActive,
        public readonly ?string $description,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}

    public function isDebitNormal(): bool
    {
        return $this->normalBalance === self::NORMAL_DEBIT;
    }

    /** Returns true when this account has no parent (i.e. it is a root-level account). */
    public function isParent(): bool
    {
        return $this->parentId === null;
    }
}
