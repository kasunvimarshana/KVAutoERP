<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;

class EloquentUserRepository implements UserRepositoryInterface
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

    public function create(array $data): User
    {
        $record = $this->model->newQuery()->create($data);

        return $this->toEntity($record);
    }

    public function update(int $id, array $data): ?User
    {
        $record = $this->model->newQueryWithoutScopes()->find($id);

        if ($record === null) {
            return null;
        }

        $record->fill($data)->save();

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

    public function allForTenant(int $tenantId): array
    {
        return $this->model
            ->newQueryWithoutScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (UserModel $m) => $this->toEntity($m))
            ->all();
    }

    public function assignRole(int $userId, int $roleId): void
    {
        DB::table('role_user')->insertOrIgnore([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }

    public function removeRole(int $userId, int $roleId): void
    {
        DB::table('role_user')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->delete();
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
            emailVerifiedAt: $model->email_verified_at,
            settings: $model->settings,
            createdAt: $model->created_at,
        );
    }
}
