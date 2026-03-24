<?php

namespace Modules\Auth\Application\Services;

use Modules\Auth\Application\Contracts\AuthorizationServiceInterface;
use Modules\Auth\Application\Contracts\AuthorizationStrategyInterface;
use Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel;

/**
 * Composite authorization service.
 * Holds a primary strategy and additional pluggable strategies.
 * Falls back through strategies until one grants access.
 */
class AuthorizationService implements AuthorizationServiceInterface
{
    /** @var AuthorizationStrategyInterface[] */
    private array $strategies = [];

    public function __construct(
        private readonly RbacAuthorizationStrategy $rbacStrategy,
        private readonly AbacAuthorizationStrategy $abacStrategy,
    ) {
        $this->strategies[] = $rbacStrategy;
        $this->strategies[] = $abacStrategy;
    }

    public function hasRole(int $userId, string $role): bool
    {
        return $this->rbacStrategy->authorize($userId, $role);
    }

    public function hasPermission(int $userId, string $permission): bool
    {
        $user = UserModel::with('roles.permissions')->find($userId);

        if (! $user) {
            return false;
        }

        foreach ($user->roles as $userRole) {
            if ($userRole->permissions->contains('name', $permission)) {
                return true;
            }
        }

        return false;
    }

    public function can(int $userId, string $ability, mixed $subject = null): bool
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->authorize($userId, $ability, $subject)) {
                return true;
            }
        }

        return false;
    }

    public function addStrategy(AuthorizationStrategyInterface $strategy): void
    {
        $this->strategies[] = $strategy;
    }
}
