<?php
declare(strict_types=1);
namespace Modules\User\Domain\Entities;
use Modules\Core\Domain\ValueObjects\Email;

class User {
    private ?int $id;
    private int $tenantId;
    private string $firstName;
    private string $lastName;
    private Email $email;
    private ?string $password;
    private array $roles;
    private array $permissions;
    private ?string $avatar;
    private array $preferences;

    public function __construct(
        int $tenantId,
        Email $email,
        string $firstName,
        string $lastName,
        ?string $password = null,
        array $roles = [],
        array $permissions = [],
        ?string $avatar = null,
        array $preferences = [],
        ?int $id = null
    ) {
        $this->tenantId = $tenantId;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->password = $password;
        $this->roles = $roles;
        $this->permissions = $permissions;
        $this->avatar = $avatar;
        $this->preferences = $preferences;
        $this->id = $id;
    }

    public function getId(): ?int { return $this->id; }
    public function getTenantId(): int { return $this->tenantId; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getEmail(): Email { return $this->email; }
    public function getAvatar(): ?string { return $this->avatar; }
    public function getPreferences(): array { return $this->preferences; }

    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->password ?? '');
    }

    public function changePassword(string $newHash): void {
        $this->password = $newHash;
    }

    public function changeAvatar(?string $path): void {
        $this->avatar = $path;
    }

    public function update(array $data): void {
        if (isset($data['first_name'])) $this->firstName = $data['first_name'];
        if (isset($data['last_name'])) $this->lastName = $data['last_name'];
        if (isset($data['preferences'])) $this->preferences = $data['preferences'];
    }

    public function hasRole(string $role): bool {
        return in_array($role, $this->roles, true);
    }

    public function hasPermission(string $permission): bool {
        return in_array($permission, $this->permissions, true);
    }
}
