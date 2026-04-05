<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Laravel\Passport\Token;
use Modules\Auth\Domain\Entities\Role;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly UserModel $model,
    ) {}

    public function findById(int $id): ?User
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        return $record ? $this->toEntity($record) : null;
    }

    public function findByEmail(string $email, ?int $tenantId = null): ?User
    {
        $query = $this->model->newQueryWithoutScopes()->where('email', $email);

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $record = $query->first();

        return $record ? $this->toEntity($record) : null;
    }

    public function findByTenantId(int $tenantId): Collection
    {
        return $this->model->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (UserModel $m) => $this->toEntity($m));
    }

    public function create(array $data): User
    {
        $record = $this->model->newInstance();
        $record->forceFill($data);
        $record->save();

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?User
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toEntity($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return false;
        }

        return (bool) $record->delete();
    }

    public function assignRole(int $userId, int $roleId): void
    {
        $record = $this->model->newQueryWithoutScopes()->findOrFail($userId);
        $record->roles()->syncWithoutDetaching([$roleId]);
    }

    public function revokeRole(int $userId, int $roleId): void
    {
        $record = $this->model->newQueryWithoutScopes()->findOrFail($userId);
        $record->roles()->detach($roleId);
    }

    public function getRoles(int $userId): Collection
    {
        $record = $this->model->newQueryWithoutScopes()->with('roles')->find($userId);

        if ($record === null) {
            return collect();
        }

        return $record->roles->map(fn (RoleModel $m) => $this->toRoleEntity($m));
    }

    public function createToken(int $userId, string $name = 'api'): string
    {
        /** @var UserModel $record */
        $record = $this->model->newQueryWithoutScopes()->findOrFail($userId);

        return $record->createToken($name)->accessToken;
    }

    public function revokeCurrentToken(int $userId): void
    {
        /** @var UserModel|null $record */
        $record = $this->model->newQueryWithoutScopes()->find($userId);

        $record?->token()?->revoke();
    }

    public function findByAccessToken(string $token): ?User
    {
        $passportToken = Token::where('id', hash('sha256', $token))
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($passportToken === null) {
            return null;
        }

        return $this->findById((int) $passportToken->user_id);
    }

    private function toEntity(UserModel $model): User
    {
        return new User(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            email: $model->email,
            password: $model->password,
            role: $model->role,
            status: $model->status,
            emailVerifiedAt: $model->email_verified_at
                ? \DateTimeImmutable::createFromMutable($model->email_verified_at->toDateTime())
                : null,
            rememberToken: $model->remember_token,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }

    private function toRoleEntity(RoleModel $model): Role
    {
        return new Role(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            guardName: $model->guard_name,
            permissions: $model->permissions,
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at->toDateTime()),
            updatedAt: \DateTimeImmutable::createFromMutable($model->updated_at->toDateTime()),
        );
    }
}
