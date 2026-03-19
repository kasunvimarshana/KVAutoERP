<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\UserServiceContract;
use App\Models\Role;
use App\Models\User;
use App\Models\UserIamMapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserService implements UserServiceContract
{
    public function findById(string $userId): ?array
    {
        $user = User::with(['roles.permissions'])->find($userId);

        return $user ? $this->toArray($user) : null;
    }

    public function findByEmail(string $email): ?array
    {
        $user = User::with(['roles.permissions'])->where('email', $email)->first();

        return $user ? $this->toArray($user) : null;
    }

    public function findByExternalId(string $externalId, string $provider): ?array
    {
        $mapping = UserIamMapping::where('external_id', $externalId)
            ->where('provider', $provider)
            ->with('user.roles.permissions')
            ->first();

        if (! $mapping || ! $mapping->user) {
            return null;
        }

        return $this->toArray($mapping->user);
    }

    public function validateCredentials(string $userId, string $password): bool
    {
        $user = User::select(['id', 'password'])->find($userId);

        if (! $user) {
            return false;
        }

        return Hash::check($password, $user->password);
    }

    public function getUserClaims(string $userId): array
    {
        $user = User::with(['roles.permissions'])->find($userId);

        if (! $user) {
            return [];
        }

        return [
            'id'              => $user->id,
            'email'           => $user->email,
            'name'            => $user->name,
            'tenant_id'       => $user->tenant_id,
            'organization_id' => $user->organization_id,
            'branch_id'       => $user->branch_id,
            'roles'           => $user->getRoleNames(),
            'permissions'     => $user->getPermissionNames(),
            'token_version'   => $user->token_version,
            'iam_provider'    => $user->iam_provider ?? 'local',
            'status'          => $user->status,
        ];
    }

    public function recordLoginEvent(string $userId, string $deviceId, string $ipAddress): void
    {
        // Fetch tenant_id once before the transaction to avoid a redundant query inside it
        $tenantId = User::select('tenant_id')->find($userId)?->tenant_id;

        DB::transaction(function () use ($userId, $deviceId, $ipAddress, $tenantId): void {
            User::where('id', $userId)->update([
                'last_login_at'     => now(),
                'last_login_ip'     => $ipAddress,
                'last_login_device' => $deviceId,
            ]);

            DB::table('audit_logs')->insert([
                'id'          => (string) Str::uuid(),
                'action'      => 'user.login',
                'entity_type' => 'user',
                'entity_id'   => $userId,
                'actor_id'    => $userId,
                'tenant_id'   => $tenantId,
                'ip_address'  => $ipAddress,
                'context'     => json_encode(['device_id' => $deviceId]),
                'created_at'  => now(),
            ]);
        });
    }

    public function incrementTokenVersion(string $userId): int
    {
        $user = User::findOrFail($userId);
        $user->increment('token_version');

        return (int) $user->fresh()->token_version;
    }

    public function createUser(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $user = User::create([
                'id'              => (string) Str::uuid(),
                'name'            => $data['name'],
                'email'           => $data['email'],
                'password'        => Hash::make($data['password']),
                'tenant_id'       => $data['tenant_id'] ?? null,
                'organization_id' => $data['organization_id'] ?? null,
                'branch_id'       => $data['branch_id'] ?? null,
                'location_id'     => $data['location_id'] ?? null,
                'department_id'   => $data['department_id'] ?? null,
                'status'          => $data['status'] ?? 'active',
                'iam_provider'    => $data['iam_provider'] ?? 'local',
                'phone'           => $data['phone'] ?? null,
                'metadata'        => $data['metadata'] ?? null,
            ]);

            // Assign initial roles if provided
            if (! empty($data['role_ids'])) {
                foreach ((array) $data['role_ids'] as $roleId) {
                    $role = Role::find($roleId);
                    if ($role) {
                        DB::table('role_user')->insertOrIgnore([
                            'user_id'     => $user->id,
                            'role_id'     => $roleId,
                            'tenant_id'   => $data['tenant_id'] ?? null,
                            'assigned_by' => $data['assigned_by'] ?? null,
                            'created_at'  => now(),
                        ]);
                    }
                }
            }

            $user->load('roles.permissions');

            Log::info('User created', ['user_id' => $user->id, 'tenant_id' => $user->tenant_id]);

            return $this->toArray($user);
        });
    }

    public function updateUser(string $userId, array $data): array
    {
        $user = User::findOrFail($userId);

        // Direct password changes are not permitted here.
        // Use a dedicated password-reset or change-password endpoint that
        // verifies the current password and re-hashes the new one.
        unset($data['password'], $data['id'], $data['email']);

        $user->update($data);
        $user->load('roles.permissions');

        return $this->toArray($user);
    }

    public function listUsers(string $tenantId, array $filters = [], int $perPage = 20): array
    {
        $query = User::with(['roles'])
            ->where('tenant_id', $tenantId);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['search'].'%');
            });
        }

        $paginated = $query->paginate($perPage);

        return [
            'data'       => $paginated->map(fn (User $u) => $this->toArray($u))->all(),
            'pagination' => [
                'total'        => $paginated->total(),
                'per_page'     => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
            ],
        ];
    }

    public function deleteUser(string $userId): void
    {
        $user = User::findOrFail($userId);
        $user->delete();
    }

    // ──────────────────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────────────────

    private function toArray(User $user): array
    {
        return [
            'id'                 => $user->id,
            'name'               => $user->name,
            'email'              => $user->email,
            'status'             => $user->status,
            'tenant_id'          => $user->tenant_id,
            'organization_id'    => $user->organization_id,
            'branch_id'          => $user->branch_id,
            'location_id'        => $user->location_id,
            'department_id'      => $user->department_id,
            'roles'              => $user->getRoleNames(),
            'permissions'        => $user->getPermissionNames(),
            'token_version'      => $user->token_version,
            'iam_provider'       => $user->iam_provider,
            'external_id'        => $user->external_id,
            'phone'              => $user->phone,
            'avatar'             => $user->avatar,
            'metadata'           => $user->metadata,
            'last_login_at'      => $user->last_login_at?->toIso8601String(),
            'last_login_ip'      => $user->last_login_ip,
            'last_login_device'  => $user->last_login_device,
            'email_verified_at'  => $user->email_verified_at?->toIso8601String(),
            'created_at'         => $user->created_at?->toIso8601String(),
            'updated_at'         => $user->updated_at?->toIso8601String(),
        ];
    }
}
