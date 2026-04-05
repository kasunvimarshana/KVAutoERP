<?php

declare(strict_types=1);

namespace Modules\CRM\Domain\Entities;

final class Contact
{
    public const TYPE_CUSTOMER  = 'customer';
    public const TYPE_SUPPLIER  = 'supplier';
    public const TYPE_PROSPECT  = 'prospect';
    public const TYPE_PARTNER   = 'partner';
    public const TYPE_OTHER     = 'other';

    public function __construct(
        public readonly int $id,
        public readonly int $tenantId,
        public readonly string $type,
        public readonly string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $mobile,
        public readonly ?string $company,
        public readonly ?string $position,
        public readonly ?array $address,
        public readonly ?string $taxNumber,
        public readonly string $currencyCode,
        public readonly float $creditLimit,
        public readonly int $paymentTerms,
        public readonly ?string $notes,
        public readonly bool $isActive,
        public readonly ?array $tags,
        public readonly ?array $customFields,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}
