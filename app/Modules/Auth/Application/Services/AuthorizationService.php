<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;

use Modules\Auth\Application\Contracts\AuthorizationServiceInterface;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;

class AuthorizationService implements AuthorizationServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function can(int $userId, string $ability, mixed $subject = null): bool
    {
        $user = $this->userRepository->find($userId);
        if (! $user) {
            return false;
        }

        // Load roles and permissions
        $user->loadMissing('roles.permissions');

        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                if ($permission->slug === $ability) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasPermissions(int $userId, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->can($userId, $permission)) {
                return false;
            }
        }

        return true;
    }

    public function hasAnyRole(int $userId, array $roles): bool
    {
        $user = $this->userRepository->find($userId);
        if (! $user) {
            return false;
        }

        $user->loadMissing('roles');

        foreach ($user->roles as $role) {
            if (in_array($role->slug, $roles, true)) {
                return true;
            }
        }

        return false;
    }
}
