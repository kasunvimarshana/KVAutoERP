<?php

declare(strict_types=1);

namespace KvSaas\Contracts\User\Dto;

/** Runtime tenant configuration loaded from the Configuration service. */
final readonly class TenantConfigDto
{
    public function __construct(
        public string $tenantId,
        public string $iamProvider        = 'local',
        /** @var array<string, mixed> */
        public array  $iamProviderConfig  = [],
        public int    $accessTokenTtl     = 900,
        public int    $refreshTokenTtl    = 2592000,
        /** @var array<string, bool> */
        public array  $featureFlags       = [],
        /** @var array<string, mixed> */
        public array  $policies           = [],
        /** @var string[] */
        public array  $allowedRoles       = [],
    ) {}

    public function isFeatureEnabled(string $flag): bool
    {
        return (bool) ($this->featureFlags[$flag] ?? false);
    }

    public function isRoleAllowed(string $role): bool
    {
        return empty($this->allowedRoles) || in_array($role, $this->allowedRoles, true);
    }
}
