<?php
declare(strict_types=1);
namespace Modules\Auth\Infrastructure\Persistence;
use Modules\Auth\Application\Contracts\AuthUserRepositoryInterface;

class EloquentAuthUserRepository implements AuthUserRepositoryInterface {
    public function findForPassport(string $username): mixed { return null; }
    public function findAuthenticatable(int $id): mixed { return null; }
    public function getEmailById(int $id): ?string { return null; }
    public function getIdByEmail(string $email): ?int { return null; }
    public function getRolesWithPermissions(int $userId): array { return []; }
    public function createUser(array $data): mixed { return null; }
}
