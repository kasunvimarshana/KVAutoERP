<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Domain\User\Entities\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email, string $tenantId): ?User;

    public function findByTenant(string $tenantId): Collection;

    public function findActiveByTenant(string $tenantId): Collection;

    public function findByEmailVerificationToken(string $token): ?User;

    public function updateLastLogin(string $userId): void;
}
