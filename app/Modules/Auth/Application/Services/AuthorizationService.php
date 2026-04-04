<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Modules\Auth\Application\Contracts\AuthorizationServiceInterface;
use Modules\Authorization\Domain\RepositoryInterfaces\UserRoleRepositoryInterface;

class AuthorizationService implements AuthorizationServiceInterface
{
    public function __construct(
        private readonly UserRoleRepositoryInterface $userRoleRepository,
    ) {}

    public function can(int $userId, string $ability, mixed $subject = null): bool
    {
        $tenantId = auth()->user()?->tenant_id ?? 0;
        if ($tenantId === 0) {
            return false;
        }

        return $this->userRoleRepository->hasPermission($userId, $tenantId, $ability);
    }
}
