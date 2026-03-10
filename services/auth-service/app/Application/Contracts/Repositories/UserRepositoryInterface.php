<?php

declare(strict_types=1);

namespace App\Application\Contracts\Repositories;

use App\Domain\Models\User;
use Shared\BaseRepository\BaseRepositoryInterface;

/**
 * User Repository Contract
 * 
 * Extends the base repository with User-specific query methods.
 * Depends on the domain model User - infrastructure depends on this, not vice versa.
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find a user by email within a specific tenant.
     */
    public function findByEmailAndTenant(string $email, string|int $tenantId): ?User;

    /**
     * Check if email exists within a tenant.
     */
    public function existsByEmailAndTenant(string $email, string|int $tenantId): bool;

    /**
     * Find user by their active OAuth token (for token validation).
     */
    public function findByToken(string $token): ?User;
}
