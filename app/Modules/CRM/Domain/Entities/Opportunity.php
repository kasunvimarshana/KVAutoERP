<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\Entities;

final class Opportunity
{
    public const STAGE_PROSPECTING   = 'prospecting';
    public const STAGE_QUALIFICATION = 'qualification';
    public const STAGE_PROPOSAL      = 'proposal';
    public const STAGE_NEGOTIATION   = 'negotiation';
    public const STAGE_WON           = 'won';
    public const STAGE_LOST          = 'lost';

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly int $contactId,
        public readonly string $name,
        public readonly string $stage,
        public readonly int $probability,
        public readonly float $value,
        public readonly ?\DateTimeImmutable $expectedCloseDate,
        public readonly ?int $assignedTo,
        public readonly ?string $notes,
        public readonly ?string $lostReason,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
