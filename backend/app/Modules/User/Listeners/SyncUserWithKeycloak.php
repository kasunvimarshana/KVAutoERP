<?php

namespace App\Modules\User\Listeners;

use App\Modules\User\Events\UserCreated;
use App\Modules\User\Events\UserDeleted;
use App\Modules\User\Events\UserUpdated;
use App\Services\KeycloakService;
use Illuminate\Support\Facades\Log;

class SyncUserWithKeycloak
{
    public function __construct(private KeycloakService $keycloakService) {}

    public function handleUserCreated(UserCreated $event): void
    {
        $user = $event->user;

        if ($user->keycloak_id) {
            return;
        }

        try {
            $adminToken = $this->keycloakService->getAdminToken();

            $this->keycloakService->createUser([
                'username'   => $user->username,
                'email'      => $user->email,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'tenant_id'  => $user->tenant_id,
                'password'   => bin2hex(random_bytes(16)),
            ], $adminToken);

            Log::info('User synced to Keycloak', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('Failed to sync user to Keycloak', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function handleUserUpdated(UserUpdated $event): void
    {
        $user = $event->user;

        if (!$user->keycloak_id) {
            return;
        }

        try {
            $adminToken = $this->keycloakService->getAdminToken();
            $this->keycloakService->updateUser($user->keycloak_id, [
                'email'      => $user->email,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'tenant_id'  => $user->tenant_id,
            ], $adminToken);
        } catch (\Exception $e) {
            Log::error('Failed to update user in Keycloak', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function handleUserDeleted(UserDeleted $event): void
    {
        $user = $event->user;

        if (!$user->keycloak_id) {
            return;
        }

        try {
            $adminToken = $this->keycloakService->getAdminToken();
            $this->keycloakService->deleteUser($user->keycloak_id, $adminToken);
        } catch (\Exception $e) {
            Log::error('Failed to delete user from Keycloak', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
