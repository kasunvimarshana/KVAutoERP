<?php

declare(strict_types=1);

namespace Modules\Accounting\Domain\Entities;

final class Refund
{
    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $paymentId,
        public readonly float $amount,
        public readonly \DateTimeImmutable $refundDate,
        public readonly ?string $reason,
        public readonly ?int $journalEntryId,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
