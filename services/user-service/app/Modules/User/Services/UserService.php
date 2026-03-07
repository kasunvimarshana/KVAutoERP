<?php

namespace App\Modules\User\Services;

use App\Modules\User\DTOs\UserDTO;
use App\Modules\User\Models\User;
use App\Modules\User\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function listUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->findAll($filters, $perPage);
    }

    public function getUser(int $id): User
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "User with ID {$id} not found."
            );
        }

        return $user;
    }

    /**
     * Create a user locally and in Keycloak.
     */
    public function createUser(UserDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {
            // Check for duplicates
            if ($this->userRepository->findByEmail($dto->email)) {
                throw new \InvalidArgumentException("Email '{$dto->email}' is already registered.");
            }

            if ($this->userRepository->findByUsername($dto->username)) {
                throw new \InvalidArgumentException("Username '{$dto->username}' is already taken.");
            }

            // Create in Keycloak
            $keycloakId = $this->createKeycloakUser($dto);

            // Create local record
            $userDtoWithKeycloak = new UserDTO(
                username:   $dto->username,
                email:      $dto->email,
                firstName:  $dto->firstName,
                lastName:   $dto->lastName,
                phone:      $dto->phone,
                roles:      $dto->roles,
                attributes: $dto->attributes,
                isActive:   $dto->isActive,
                keycloakId: $keycloakId,
            );

            $user = $this->userRepository->create($userDtoWithKeycloak);

            Log::info('User created', ['user_id' => $user->id, 'email' => $user->email]);

            return $user;
        });
    }

    public function updateUser(int $id, UserDTO $dto): User
    {
        return DB::transaction(function () use ($id, $dto) {
            $existing = $this->userRepository->findById($id);

            if (!$existing) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                    "User with ID {$id} not found."
                );
            }

            // Check email uniqueness
            $byEmail = $this->userRepository->findByEmail($dto->email);
            if ($byEmail && $byEmail->id !== $id) {
                throw new \InvalidArgumentException("Email '{$dto->email}' is already in use.");
            }

            // Update in Keycloak if keycloak_id exists
            if ($existing->keycloak_id) {
                $this->updateKeycloakUser($existing->keycloak_id, $dto);
            }

            $user = $this->userRepository->update($id, $dto);

            Log::info('User updated', ['user_id' => $user->id]);

            return $user;
        });
    }

    public function deleteUser(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $user = $this->userRepository->findById($id);

            if (!$user) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                    "User with ID {$id} not found."
                );
            }

            // Deactivate in Keycloak
            if ($user->keycloak_id) {
                $this->deactivateKeycloakUser($user->keycloak_id);
            }

            return $this->userRepository->delete($id);
        });
    }

    /**
     * Sync user data from Keycloak token.
     */
    public function syncFromKeycloak(string $keycloakId, array $tokenData): User
    {
        return $this->userRepository->syncWithKeycloak($keycloakId, $tokenData);
    }

    /**
     * Check RBAC: user has the given role.
     */
    public function userHasRole(int $userId, string $role): bool
    {
        $user = $this->userRepository->findById($userId);
        return $user ? $user->hasRole($role) : false;
    }

    /**
     * Check ABAC: user has the given attribute.
     */
    public function userHasAttribute(int $userId, string $key, mixed $value): bool
    {
        $user = $this->userRepository->findById($userId);
        return $user ? $user->hasAttribute($key, $value) : false;
    }

    private function createKeycloakUser(UserDTO $dto): ?string
    {
        try {
            $adminToken = $this->getKeycloakAdminToken();

            $response = Http::withToken($adminToken)
                ->post(config('keycloak.admin_url') . '/users', [
                    'username'  => $dto->username,
                    'email'     => $dto->email,
                    'firstName' => $dto->firstName,
                    'lastName'  => $dto->lastName,
                    'enabled'   => $dto->isActive,
                    'credentials' => $dto->password ? [[
                        'type'      => 'password',
                        'value'     => $dto->password,
                        'temporary' => false,
                    ]] : [],
                ]);

            if ($response->status() === 201) {
                // Extract user ID from Location header
                $location = $response->header('Location');
                return basename($location);
            }

            Log::warning('Keycloak user creation failed', ['status' => $response->status()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Keycloak user creation error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function updateKeycloakUser(string $keycloakId, UserDTO $dto): void
    {
        try {
            $adminToken = $this->getKeycloakAdminToken();

            Http::withToken($adminToken)
                ->put(config('keycloak.admin_url') . "/users/{$keycloakId}", [
                    'firstName' => $dto->firstName,
                    'lastName'  => $dto->lastName,
                    'email'     => $dto->email,
                    'enabled'   => $dto->isActive,
                ]);
        } catch (\Exception $e) {
            Log::error('Keycloak user update error', ['error' => $e->getMessage()]);
        }
    }

    private function deactivateKeycloakUser(string $keycloakId): void
    {
        try {
            $adminToken = $this->getKeycloakAdminToken();
            Http::withToken($adminToken)
                ->put(config('keycloak.admin_url') . "/users/{$keycloakId}", ['enabled' => false]);
        } catch (\Exception $e) {
            Log::error('Keycloak user deactivation error', ['error' => $e->getMessage()]);
        }
    }

    private function getKeycloakAdminToken(): string
    {
        $response = Http::asForm()->post(
            config('keycloak.base_url') . '/realms/master/protocol/openid-connect/token',
            [
                'grant_type'    => 'client_credentials',
                'client_id'     => config('keycloak.admin_client_id'),
                'client_secret' => config('keycloak.admin_client_secret'),
            ]
        );

        return $response->json('access_token', '');
    }
}
