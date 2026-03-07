<?php

namespace App\Modules\User\Services;

use App\Modules\User\DTOs\UserDTO;
use App\Modules\User\Events\UserCreated;
use App\Modules\User\Events\UserDeleted;
use App\Modules\User\Events\UserUpdated;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function list(string $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->userRepository->paginate($tenantId, $perPage, $filters);
    }

    public function findById(string $id, string $tenantId): User
    {
        $user = $this->userRepository->findById($id, $tenantId);

        if (!$user) {
            throw new \RuntimeException("User not found: {$id}");
        }

        return $user;
    }

    public function create(UserDTO $dto): User
    {
        $data       = $dto->toArray();
        $data['id'] = Str::uuid()->toString();

        $user = $this->userRepository->create($data);

        Event::dispatch(new UserCreated($user));

        return $user;
    }

    public function update(string $id, string $tenantId, array $data): User
    {
        $user = $this->findById($id, $tenantId);

        $updated = $this->userRepository->update($user, $data);

        Event::dispatch(new UserUpdated($updated));

        return $updated;
    }

    public function delete(string $id, string $tenantId): bool
    {
        $user = $this->findById($id, $tenantId);

        Event::dispatch(new UserDeleted($user));

        return $this->userRepository->delete($user);
    }

    public function restore(string $id): bool
    {
        return $this->userRepository->restore($id);
    }
}
