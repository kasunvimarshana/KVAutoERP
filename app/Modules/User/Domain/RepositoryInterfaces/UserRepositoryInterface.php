<?php
declare(strict_types=1);
namespace Modules\User\Domain\RepositoryInterfaces;
use Modules\User\Domain\Entities\User;

interface UserRepositoryInterface {
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function verifyPassword(int $id, string $password): bool;
    public function changePassword(int $id, string $newHash): void;
    public function updateAvatar(int $id, string $path): void;
    public function save(User $user): User;
}
