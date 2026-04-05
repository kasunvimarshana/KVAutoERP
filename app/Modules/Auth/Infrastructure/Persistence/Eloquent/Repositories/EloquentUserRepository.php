<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Collection;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Modules\Core\Domain\Exceptions\NotFoundException;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly UserModel $model) {}

    public function findById(string $id): ?User
    {
        $model = $this->model->withoutGlobalScopes()->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByEmail(string $email, string $tenantId): ?User
    {
        $model = $this->model->withoutGlobalScopes()
            ->where('email', $email)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function create(array $data): User
    {
        $model = $this->model->create($data);

        return $this->toEntity($model);
    }

    public function update(string $id, array $data): User
    {
        $model = $this->model->withoutGlobalScopes()->findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(string $id): bool
    {
        $model = $this->model->withoutGlobalScopes()->find($id);

        if (! $model) {
            throw new NotFoundException('User', $id);
        }

        return (bool) $model->delete();
    }

    public function allByTenant(string $tenantId): Collection
    {
        return $this->model->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->get()
            ->map(fn (UserModel $m) => $this->toEntity($m));
    }

    /** Retrieve the raw Eloquent model (needed for auth). */
    public function findModelById(string $id): ?UserModel
    {
        return $this->model->withoutGlobalScopes()->find($id);
    }

    /** Retrieve raw model by email + tenant (needed for auth). */
    public function findModelByEmail(string $email, string $tenantId): ?UserModel
    {
        return $this->model->withoutGlobalScopes()
            ->where('email', $email)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    private function toEntity(UserModel $model): User
    {
        return new User(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            email: $model->email,
            role: $model->role_id ?? 'user',
            status: $model->status,
            preferences: $model->preferences ?? [],
            lastLoginAt: $model->last_login_at?->toDateTimeImmutable(),
            createdAt: $model->created_at?->toDateTimeImmutable(),
        );
    }
}
