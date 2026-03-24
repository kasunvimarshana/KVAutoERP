<?php

namespace Modules\Auth\Application\Services;

use Modules\Auth\Application\Contracts\AuthorizationStrategyInterface;
use Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel;

/**
 * Role-Based Access Control (RBAC) authorization strategy.
 * Checks whether a user has a given role or permission via the roles/permissions tables.
 */
class RbacAuthorizationStrategy implements AuthorizationStrategyInterface
{
    public function getName(): string
    {
        return 'rbac';
    }

    public function authorize(int $userId, string $ability, mixed $subject = null): bool
    {
        $user = UserModel::with('roles.permissions')->find($userId);

        if (!$user) {
            return false;
        }

        foreach ($user->roles as $role) {
            // Direct role name match
            if (strtolower($role->name) === strtolower($ability)) {
                return true;
            }

            // Permission name match on the role
            if ($role->permissions->contains('name', $ability)) {
                return true;
            }
        }

        return false;
    }
}
