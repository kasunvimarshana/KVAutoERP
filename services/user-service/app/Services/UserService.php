<?php

namespace App\Services;

use App\DTOs\UserDTO;
use App\Events\UserCreated;
use App\Events\UserDeleted;
use App\Events\UserUpdated;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(private readonly UserRepository $userRepository) {}

    public function listUsers(string $tenantId, array $filters = [], int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $repo = $this->userRepository->withTenant($tenantId);

        if (!empty($filters['search'])) {
            return $repo->search($filters['search'], ['name', 'email'])
                ->paginate($perPage, ['*'], 'page', $page);
        }

        if (!empty($filters['role'])) {
            return $repo->filter([['column' => 'role', 'operator' => 'eq', 'value' => $filters['role']]])
                ->paginate($perPage, ['*'], 'page', $page);
        }

        if (!empty($filters['status'])) {
            return $repo->filter([['column' => 'status', 'operator' => 'eq', 'value' => $filters['status']]])
                ->paginate($perPage, ['*'], 'page', $page);
        }

        return $repo->getWithPagination($perPage, $page);
    }

    public function getUser(string $tenantId, string $userId): ?UserDTO
    {
        $user = $this->userRepository->withTenant($tenantId)->find($userId);

        if ($user === null) {
            return null;
        }

        return UserDTO::fromModel($user);
    }

    public function createUser(string $tenantId, array $data): UserDTO
    {
        $this->ensureEmailUnique($tenantId, $data['email']);

        $user = DB::transaction(function () use ($tenantId, $data): User {
            $user = $this->userRepository->create([
                'tenant_id'   => $tenantId,
                'name'        => $data['name'],
                'email'       => $data['email'],
                'password'    => Hash::make($data['password']),
                'role'        => $data['role'] ?? 'user',
                'permissions' => $data['permissions'] ?? [],
                'status'      => $data['status'] ?? 'active',
            ]);

            event(new UserCreated($user));

            return $user;
        });

        return UserDTO::fromModel($user);
    }

    public function updateUser(string $tenantId, string $userId, array $data): ?UserDTO
    {
        $user = $this->userRepository->withTenant($tenantId)->find($userId);

        if ($user === null) {
            return null;
        }

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $this->ensureEmailUnique($tenantId, $data['email'], $userId);
        }

        $updated = DB::transaction(function () use ($user, $data): User {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->fill($data)->save();

            event(new UserUpdated($user->fresh()));

            return $user->fresh();
        });

        return UserDTO::fromModel($updated);
    }

    public function deleteUser(string $tenantId, string $userId): bool
    {
        $user = $this->userRepository->withTenant($tenantId)->find($userId);

        if ($user === null) {
            return false;
        }

        return DB::transaction(function () use ($user, $tenantId, $userId): bool {
            $deleted = $this->userRepository->delete($userId);

            if ($deleted) {
                event(new UserDeleted($userId, $tenantId));
            }

            return $deleted;
        });
    }

    public function assignRole(string $tenantId, string $userId, string $role): ?UserDTO
    {
        $allowedRoles = ['admin', 'manager', 'user'];

        if (!in_array($role, $allowedRoles, true)) {
            throw new \InvalidArgumentException("Invalid role: {$role}");
        }

        $updated = DB::transaction(function () use ($tenantId, $userId, $role): ?User {
            $user = $this->userRepository->withTenant($tenantId)->find($userId);

            if ($user === null) {
                return null;
            }

            $user->role = $role;
            $user->save();

            event(new UserUpdated($user->fresh()));

            return $user->fresh();
        });

        return $updated ? UserDTO::fromModel($updated) : null;
    }

    public function updatePermissions(string $tenantId, string $userId, array $permissions): ?UserDTO
    {
        $updated = DB::transaction(function () use ($tenantId, $userId, $permissions): ?User {
            $user = $this->userRepository->withTenant($tenantId)->find($userId);

            if ($user === null) {
                return null;
            }

            $user->permissions = $permissions;
            $user->save();

            event(new UserUpdated($user->fresh()));

            return $user->fresh();
        });

        return $updated ? UserDTO::fromModel($updated) : null;
    }

    private function ensureEmailUnique(string $tenantId, string $email, ?string $excludeId = null): void
    {
        $query = $this->userRepository->withTenant($tenantId)
            ->filter([['column' => 'email', 'operator' => 'eq', 'value' => $email]]);

        if ($excludeId !== null) {
            $query = $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'email' => ['The email address is already in use within this tenant.'],
            ]);
        }
    }
}
