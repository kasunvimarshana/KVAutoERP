<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\Entities;

final class Lead
{
    public const SOURCE_WEBSITE   = 'website';
    public const SOURCE_REFERRAL  = 'referral';
    public const SOURCE_SOCIAL    = 'social';
    public const SOURCE_EMAIL     = 'email';
    public const SOURCE_COLD_CALL = 'cold_call';
    public const SOURCE_EVENT     = 'event';
    public const SOURCE_OTHER     = 'other';

    public const STATUS_NEW          = 'new';
    public const STATUS_CONTACTED    = 'contacted';
    public const STATUS_QUALIFIED    = 'qualified';
    public const STATUS_DISQUALIFIED = 'disqualified';
    public const STATUS_CONVERTED    = 'converted';

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly ?int $contactId,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly ?string $company,
        public readonly string $source,
        public readonly string $status,
        public readonly int $score,
        public readonly ?int $assignedTo,
        public readonly ?string $notes,
        public readonly ?float $expectedValue,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
