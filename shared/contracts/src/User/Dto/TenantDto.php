<?php

declare(strict_types=1);

namespace KvSaas\Contracts\User\Dto;

final readonly class TenantDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public string $status,
        public string $iamProvider    = 'local',
        /** @var array<string, mixed> */
        public array  $configuration  = [],
    ) {}

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasFederatedIam(): bool
    {
        return $this->iamProvider !== 'local';
    }
}
