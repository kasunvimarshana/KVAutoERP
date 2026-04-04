<?php

declare(strict_types=1);

namespace Modules\Auth\Application\Services;

use Modules\Auth\Application\Contracts\AuthorizationServiceInterface;
use Modules\Auth\Domain\Repositories\UserRoleRepositoryInterface;

class AuthorizationService implements AuthorizationServiceInterface
{
    public function __construct(
        private readonly UserRoleRepositoryInterface $userRoleRepository,
    ) {}

    public function can(int $userId, string $ability, mixed $subject = null): bool
    {
        $tenantId = $this->resolveTenantId($subject);

        if ($tenantId === null) {
            return false;
        }

        $permissions = $this->userRoleRepository->getUserPermissions($userId, $tenantId);

        return in_array($ability, $permissions, true);
    }

    private function resolveTenantId(mixed $subject): ?int
    {
        if (is_null($subject)) {
            try {
                $user = auth()->user();
                return $user ? (int) ($user->tenant_id ?? 0) : null;
            } catch (\Throwable) {
                return null;
            }
        }

        if (is_object($subject) && property_exists($subject, 'tenant_id')) {
            return (int) $subject->tenant_id;
        }

        if (is_object($subject) && property_exists($subject, 'tenantId')) {
            return (int) $subject->tenantId;
        }

        return null;
    }
}
