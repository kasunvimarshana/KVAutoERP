<?php declare(strict_types=1);
namespace Modules\Auth\Application\Contracts;
use Modules\Auth\Domain\Entities\User;
interface UserServiceInterface {
    public function register(array $data): User;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function update(int $id, array $data): User;
    public function delete(int $id): void;
}
