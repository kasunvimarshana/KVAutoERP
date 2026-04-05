<?php declare(strict_types=1);
namespace Modules\Auth\Application\Services;
use Modules\Auth\Application\Contracts\UserServiceInterface;
use Modules\Auth\Domain\Entities\User;
use Modules\Auth\Domain\RepositoryInterfaces\UserRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;
class UserService implements UserServiceInterface {
    public function __construct(private readonly UserRepositoryInterface $repo) {}
    public function register(array $data): User {
        $user = new User(
            null,
            $data['tenant_id'],
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['role'] ?? 'staff',
            true,
            null,
            new \DateTimeImmutable(),
        );
        return $this->repo->save($user);
    }
    public function findById(int $id): ?User { return $this->repo->findById($id); }
    public function findByEmail(string $email): ?User { return $this->repo->findByEmail($email); }
    public function update(int $id, array $data): User {
        $user = $this->repo->findById($id);
        if (!$user) throw new NotFoundException("User {$id} not found");
        $updated = new User(
            $user->getId(),
            $user->getTenantId(),
            $data['name'] ?? $user->getName(),
            $data['email'] ?? $user->getEmail(),
            isset($data['password']) ? password_hash($data['password'], PASSWORD_BCRYPT) : $user->getPasswordHash(),
            $data['role'] ?? $user->getRole(),
            $data['is_active'] ?? $user->isActive(),
            $user->getEmailVerifiedAt(),
            $user->getCreatedAt(),
        );
        return $this->repo->save($updated);
    }
    public function delete(int $id): void { $this->repo->delete($id); }
}
