<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email, string $tenantId): ?User
    {
        return User::forTenant($tenantId)
            ->where('email', $email)
            ->with(['roles.permissions', 'directPermissions'])
            ->first();
    }

    public function findByEmailGlobal(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(string $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user->fresh();
    }

    public function delete(string $id): bool
    {
        return (bool) User::findOrFail($id)->delete();
    }

    public function incrementTokenVersion(string $userId): void
    {
        User::where('id', $userId)->increment('token_version');
    }

    public function incrementFailedLoginAttempts(string $userId): void
    {
        User::where('id', $userId)->increment('failed_login_attempts');
    }

    public function resetFailedLoginAttempts(string $userId): void
    {
        User::where('id', $userId)->update(['failed_login_attempts' => 0]);
    }

    public function lockUser(string $userId, int $durationMinutes): void
    {
        User::where('id', $userId)->update([
            'is_locked'    => true,
            'locked_until' => now()->addMinutes($durationMinutes),
        ]);
    }

    public function unlockUser(string $userId): void
    {
        User::where('id', $userId)->update([
            'is_locked'    => false,
            'locked_until' => null,
        ]);
    }

    public function findByTenant(string $tenantId, int $perPage = 15): LengthAwarePaginator
    {
        return User::forTenant($tenantId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function existsByEmail(string $email, string $tenantId): bool
    {
        return User::forTenant($tenantId)->where('email', $email)->exists();
    }

    public function updatePassword(string $userId, string $hashedPassword): void
    {
        User::where('id', $userId)->update([
            'password'            => $hashedPassword,
            'password_changed_at' => now(),
        ]);
    }

    public function updateLastLoginAt(string $userId, string $ipAddress): void
    {
        User::where('id', $userId)->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);
    }
}
