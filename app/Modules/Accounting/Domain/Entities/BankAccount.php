<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class BankAccount
{
    public const ACCOUNT_TYPE_CHECKING        = 'checking';
    public const ACCOUNT_TYPE_SAVINGS         = 'savings';
    public const ACCOUNT_TYPE_CREDIT_CARD     = 'credit_card';
    public const ACCOUNT_TYPE_LINE_OF_CREDIT  = 'line_of_credit';
    public const ACCOUNT_TYPE_PAYPAL          = 'paypal';
    public const ACCOUNT_TYPE_OTHER           = 'other';

    public const ACCOUNT_TYPES = [
        self::ACCOUNT_TYPE_CHECKING,
        self::ACCOUNT_TYPE_SAVINGS,
        self::ACCOUNT_TYPE_CREDIT_CARD,
        self::ACCOUNT_TYPE_LINE_OF_CREDIT,
        self::ACCOUNT_TYPE_PAYPAL,
        self::ACCOUNT_TYPE_OTHER,
    ];

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $accountId,
        public readonly string $name,
        public readonly ?string $accountNumber,
        public readonly string $accountType,
        public readonly string $currencyCode,
        public readonly float $currentBalance,
        public readonly ?\DateTimeImmutable $lastSyncedAt,
        public readonly bool $isActive,
        public readonly ?array $credentials,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
