<?php

namespace App\Modules\User\Services;

use App\Modules\User\DTOs\UserDTO;
use App\Modules\User\Events\UserCreated;
use App\Modules\User\Events\UserDeleted;
use App\Modules\User\Events\UserUpdated;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function list(array $filters = [], int $perPage = 15)
    {
        return $this->userRepository->all($filters, $perPage);
    }

    public function get(int $id): User
    {
        return $this->userRepository->find($id);
    }

    public function create(UserDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {
            $user = $this->userRepository->create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
                'tenant_id' => $dto->tenantId,
                'attributes' => $dto->attributes,
                'is_active' => $dto->isActive ?? true,
            ]);

            if ($dto->role) {
                $user->assignRole($dto->role);
            }

            event(new UserCreated($user));

            return $user;
        });
    }

    public function update(int $id, UserDTO $dto): User
    {
        return DB::transaction(function () use ($id, $dto) {
            $data = array_filter([
                'name' => $dto->name,
                'email' => $dto->email,
                'attributes' => $dto->attributes,
                'is_active' => $dto->isActive,
            ], fn($v) => $v !== null);

            if ($dto->password) {
                $data['password'] = Hash::make($dto->password);
            }

            $user = $this->userRepository->update($id, $data);

            if ($dto->role) {
                $user->syncRoles([$dto->role]);
            }

            event(new UserUpdated($user));

            return $user;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $user = $this->userRepository->find($id);
            $result = $this->userRepository->delete($id);
            event(new UserDeleted($user));
            return $result;
        });
    }
}
