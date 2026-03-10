<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Application\Contracts\Repositories\UserRepositoryInterface;
use App\Domain\Models\User;
use Shared\BaseRepository\BaseRepository;

/**
 * User Repository Implementation
 * 
 * Concrete implementation of UserRepositoryInterface.
 * Extends BaseRepository for all dynamic CRUD operations.
 * 
 * This class lives in the Infrastructure layer and depends on
 * the Eloquent model - an infrastructure concern.
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Columns that can be searched with the `search` parameter.
     */
    protected array $searchableColumns = ['name', 'email'];

    /**
     * Columns that can be used for filtering.
     */
    protected array $filterableColumns = ['tenant_id', 'role', 'is_active', 'email'];

    /**
     * Columns that can be sorted.
     */
    protected array $sortableColumns = ['name', 'email', 'created_at', 'last_login_at', 'role'];

    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a user by email within a specific tenant.
     * 
     * @param string $email
     * @param string|int $tenantId
     * @return User|null
     */
    public function findByEmailAndTenant(string $email, string|int $tenantId): ?User
    {
        return $this->newQuery()
            ->where('email', $email)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Check if email exists within a tenant.
     * 
     * @param string $email
     * @param string|int $tenantId
     * @return bool
     */
    public function existsByEmailAndTenant(string $email, string|int $tenantId): bool
    {
        return $this->newQuery()
            ->where('email', $email)
            ->where('tenant_id', $tenantId)
            ->exists();
    }

    /**
     * Find user by their active OAuth token.
     * 
     * Passport stores the token UUID as the `id` in `oauth_access_tokens`.
     * The bearer token returned from `createToken()` is this UUID directly.
     * 
     * @param string $token
     * @return User|null
     */
    public function findByToken(string $token): ?User
    {
        return $this->newQuery()
            ->whereHas('tokens', function ($query) use ($token) {
                $query->where('id', $token)
                      ->where('revoked', false)
                      ->where(function ($q) {
                          $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                      });
            })
            ->first();
    }
}
