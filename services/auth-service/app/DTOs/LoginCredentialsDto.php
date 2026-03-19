<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class LoginCredentialsDto
{
    public function __construct(
        public string $email,
        public string $password,
        public string $tenantId,
        public string $deviceId,
        public string $deviceName,
        public string $ipAddress,
        public string $userAgent,
        public ?string $organisationId = null,
        public ?string $branchId = null,
        public bool $rememberMe = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            tenantId: $data['tenant_id'],
            deviceId: $data['device_id'],
            deviceName: $data['device_name'] ?? 'Unknown Device',
            ipAddress: $data['ip_address'] ?? '',
            userAgent: $data['user_agent'] ?? '',
            organisationId: $data['organisation_id'] ?? null,
            branchId: $data['branch_id'] ?? null,
            rememberMe: (bool) ($data['remember_me'] ?? false),
        );
    }
}
