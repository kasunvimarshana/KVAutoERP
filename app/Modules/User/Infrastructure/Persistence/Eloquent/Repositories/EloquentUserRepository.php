<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Facades\Hash;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly UserModel $model,
    ) {}

    public function findById(int $id): ?User
    {
        $model = $this->model->newQuery()->withoutGlobalScopes()->find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $model = $this->model->newQuery()->withoutGlobalScopes()->where('email', $email)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findAllByTenant(int $tenantId, int $page = 1, int $perPage = 15): array
    {
        $paginator = $this->model->newQuery()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => array_map([$this, 'toEntity'], $paginator->items()),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    public function save(User $user): User
    {
        if ($user->id === null) {
            $model = $this->model->newQuery()->create($this->toArray($user));
        } else {
            $model = $this->model->newQuery()->withoutGlobalScopes()->findOrFail($user->id);
            $model->update($this->toArray($user));
            $model->refresh();
        }

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        $this->model->newQuery()->withoutGlobalScopes()->findOrFail($id)->delete();
    }

    public function verifyPassword(int $userId, string $password): bool
    {
        $model = $this->model->newQuery()->withoutGlobalScopes()->findOrFail($userId);

        return Hash::check($password, $model->password);
    }

    public function changePassword(int $userId, string $newHashedPassword): void
    {
        $this->model->newQuery()->withoutGlobalScopes()->findOrFail($userId)->update([
            'password' => $newHashedPassword,
        ]);
    }

    public function updateAvatar(int $userId, string $avatarPath): void
    {
        $this->model->newQuery()->withoutGlobalScopes()->findOrFail($userId)->update([
            'avatar' => $avatarPath,
        ]);
    }

    private function toEntity(UserModel $model): User
    {
        return new User(
            id: $model->id,
            tenantId: $model->tenant_id,
            orgUnitId: $model->org_unit_id,
            name: $model->name,
            email: $model->email,
            password: $model->password,
            avatar: $model->avatar,
            phone: $model->phone,
            locale: $model->locale,
            timezone: $model->timezone,
            status: $model->status,
            preferences: $model->preferences ?? [],
            emailVerifiedAt: $model->email_verified_at,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }

    private function toArray(User $user): array
    {
        return [
            'tenant_id' => $user->tenantId,
            'org_unit_id' => $user->orgUnitId,
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
            'avatar' => $user->avatar,
            'phone' => $user->phone,
            'locale' => $user->locale,
            'timezone' => $user->timezone,
            'status' => $user->status,
            'preferences' => $user->preferences ? json_encode($user->preferences) : null,
            'email_verified_at' => $user->emailVerifiedAt,
        ];
    }
}
