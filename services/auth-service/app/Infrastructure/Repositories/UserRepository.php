<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Domain\User\Entities\User;
use App\Support\Repository\BaseRepository;
use Illuminate\Support\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /** @var class-string<User> */
    protected string $model = User::class;

    public function findByEmail(string $email, string $tenantId): ?User
    {
        /** @var User|null */
        return $this->newQuery()
            ->where('email', $email)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public function findByTenant(string $tenantId): Collection
    {
        return $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findActiveByTenant(string $tenantId): Collection
    {
        return $this->newQuery()
            ->where('tenant_id', $tenantId)
            ->where('status', User::STATUS_ACTIVE)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByEmailVerificationToken(string $token): ?User
    {
        /** @var User|null */
        return $this->newQuery()
            ->whereJsonContains('metadata->email_verification_token', $token)
            ->first();
    }

    public function updateLastLogin(string $userId): void
    {
        $this->newQuery()
            ->where('id', $userId)
            ->update(['last_login_at' => now()]);
    }
}
