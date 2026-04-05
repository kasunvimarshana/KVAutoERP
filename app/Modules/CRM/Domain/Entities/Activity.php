<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\Entities;

final class Activity
{
    public const TYPE_CALL    = 'call';
    public const TYPE_EMAIL   = 'email';
    public const TYPE_MEETING = 'meeting';
    public const TYPE_TASK    = 'task';
    public const TYPE_NOTE    = 'note';

    public const STATUS_PLANNED   = 'planned';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly ?int $contactId,
        public readonly ?int $opportunityId,
        public readonly ?int $leadId,
        public readonly string $type,
        public readonly string $subject,
        public readonly ?string $description,
        public readonly string $status,
        public readonly ?\DateTimeImmutable $scheduledAt,
        public readonly ?\DateTimeImmutable $completedAt,
        public readonly ?int $assignedTo,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
