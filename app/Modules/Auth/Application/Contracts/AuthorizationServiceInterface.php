<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Contracts;

interface AuthorizationServiceInterface
{
    /**
     * Determine if the given user can perform the given ability on the subject.
     */
    public function can(int $userId, string $ability, mixed $subject = null): bool;

    /**
     * Determine if the given user has ALL of the given permissions.
     */
    public function hasPermissions(int $userId, array $permissions): bool;

    /**
     * Determine if the given user has ANY of the given roles.
     */
    public function hasAnyRole(int $userId, array $roles): bool;
}
