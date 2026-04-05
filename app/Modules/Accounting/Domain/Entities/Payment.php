<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class Payment
{
    public const METHOD_CASH          = 'cash';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';
    public const METHOD_CREDIT_CARD   = 'credit_card';
    public const METHOD_CHEQUE        = 'cheque';
    public const METHOD_OTHER         = 'other';

    public const METHODS = [
        self::METHOD_CASH,
        self::METHOD_BANK_TRANSFER,
        self::METHOD_CREDIT_CARD,
        self::METHOD_CHEQUE,
        self::METHOD_OTHER,
    ];

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $referenceNo,
        public readonly \DateTimeImmutable $date,
        public readonly float $amount,
        public readonly string $currencyCode,
        public readonly string $paymentMethod,
        public readonly ?int $bankAccountId,
        public readonly ?int $journalEntryId,
        public readonly string $payableType,
        public readonly int $payableId,
        public readonly ?string $notes,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
