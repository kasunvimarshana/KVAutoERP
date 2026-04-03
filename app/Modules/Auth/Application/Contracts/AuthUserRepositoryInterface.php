<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Contracts;

interface AuthUserRepositoryInterface {
    public function findForPassport(string $username): mixed;
    public function findAuthenticatable(int $id): mixed;
    public function getEmailById(int $id): ?string;
    public function getIdByEmail(string $email): ?int;
    public function getRolesWithPermissions(int $userId): array;
    public function createUser(array $data): mixed;
}
