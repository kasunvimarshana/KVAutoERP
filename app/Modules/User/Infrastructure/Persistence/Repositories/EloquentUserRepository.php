<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Persistence\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\Repositories\UserRepositoryInterface;
use Modules\User\Domain\ValueObjects\UserStatus;
use Modules\User\Infrastructure\Persistence\Models\UserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly UserModel $model,
    ) {}

    public function findById(int $id): ?User
    {
        $model = $this->model->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $model = $this->model->where('email', $email)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByTenant(int $tenantId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $query = $tenantId > 0
            ? $this->model->where('tenant_id', $tenantId)
            : $this->model->newQuery();

        return $query
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (UserModel $m) => $this->toEntity($m));
    }

    public function create(array $data): User
    {
        $model = $this->model->create($data);

        return $this->toEntity($model);
    }

    public function update(int $id, array $data): User
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        $model = $this->model->find($id);

        return $model ? (bool) $model->delete() : false;
    }

    public function verifyPassword(int $id, string $password): bool
    {
        $model = $this->model->find($id);

        return $model && Hash::check($password, $model->password);
    }

    public function changePassword(int $id, string $newPassword): bool
    {
        $model = $this->model->find($id);

        if (! $model) {
            return false;
        }

        return (bool) $model->update(['password' => Hash::make($newPassword)]);
    }

    public function updateAvatar(int $id, ?string $avatarPath): User
    {
        $model = $this->model->findOrFail($id);
        $model->update(['avatar' => $avatarPath]);

        return $this->toEntity($model->fresh());
    }

    private function toEntity(UserModel $model): User
    {
        return new User(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            email: $model->email,
            password: $model->password,
            avatar: $model->avatar,
            timezone: $model->timezone,
            locale: $model->locale,
            status: UserStatus::from($model->status),
        );
    }
}
