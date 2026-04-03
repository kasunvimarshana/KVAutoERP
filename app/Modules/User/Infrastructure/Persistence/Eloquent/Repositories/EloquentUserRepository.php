<?php
declare(strict_types=1);
namespace Modules\User\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\User\Domain\Entities\User;
use Modules\User\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel;

class EloquentUserRepository implements UserRepositoryInterface {
    public function find(int $id): ?User { return null; }
    public function findById(int $id): ?User { return null; }
    public function findByEmail(string $email): ?User { return null; }
    public function verifyPassword(int $id, string $password): bool { return false; }
    public function changePassword(int $id, string $newHash): void {}
    public function updateAvatar(int $id, string $path): void {}
    public function save(User $user): User { return $user; }
    public function paginate(int $perPage = 15, array $filters = []): mixed { return null; }
}
