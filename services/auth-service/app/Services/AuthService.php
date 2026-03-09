<?php

declare(strict_types=1);

namespace App\Services;

use App\Application\Auth\Commands\LoginCommand;
use App\Application\Auth\Commands\LogoutCommand;
use App\Application\Auth\Commands\RefreshTokenCommand;
use App\Application\Auth\Commands\RegisterCommand;
use App\Application\Auth\Handlers\LoginCommandHandler;
use App\Application\Auth\Handlers\LogoutCommandHandler;
use App\Application\Auth\Handlers\RefreshTokenCommandHandler;
use App\Application\Auth\Handlers\RegisterCommandHandler;
use App\Application\Auth\Queries\GetUsersQuery;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Shared\Base\BaseService;
use App\Shared\Contracts\MessageBrokerInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Psr\Log\LoggerInterface;

/**
 * Auth Application Service.
 *
 * Orchestrates application-layer use cases (login, register, logout, refresh,
 * user queries) and bridges domain events to the message broker for
 * async inter-service communication.
 */
final class AuthService extends BaseService
{
    public function __construct(
        UserRepositoryInterface $repository,
        private readonly LoginCommandHandler $loginHandler,
        private readonly RegisterCommandHandler $registerHandler,
        private readonly LogoutCommandHandler $logoutHandler,
        private readonly RefreshTokenCommandHandler $refreshHandler,
        private readonly ?MessageBrokerInterface $messageBroker = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($repository, null, $logger);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Use cases
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Authenticate a user and return token + profile data.
     *
     * @return array{access_token: string, refresh_token: string|null, token_type: string, expires_in: int, user: array}
     */
    public function login(LoginCommand $command): array
    {
        $result = $this->loginHandler->handle($command);

        $this->publishEvent('auth.user.logged_in', [
            'user_id'   => $result['user']['id'] ?? null,
            'tenant_id' => $command->tenantId,
            'email'     => $command->email,
        ]);

        $this->logger->info('[AuthService] User logged in', [
            'email'     => $command->email,
            'tenant_id' => $command->tenantId,
        ]);

        return $result;
    }

    /**
     * Register a new user account.
     *
     * @return array<string, mixed>
     */
    public function register(RegisterCommand $command): array
    {
        $result = $this->registerHandler->handle($command);

        $this->publishEvent('auth.user.registered', [
            'user_id'   => $result['id'] ?? null,
            'tenant_id' => $command->tenantId,
            'email'     => $command->email,
            'name'      => $command->name,
        ]);

        $this->logger->info('[AuthService] User registered', [
            'email'     => $command->email,
            'tenant_id' => $command->tenantId,
        ]);

        return $result;
    }

    /**
     * Revoke the user's token(s).
     */
    public function logout(LogoutCommand $command): void
    {
        $this->logoutHandler->handle($command);

        $this->publishEvent('auth.user.logged_out', [
            'user_id'     => $command->userId,
            'tenant_id'   => $command->tenantId,
            'all_devices' => $command->allDevices,
        ]);

        $this->logger->info('[AuthService] User logged out', [
            'user_id'   => $command->userId,
            'tenant_id' => $command->tenantId,
        ]);
    }

    /**
     * Refresh an access token.
     *
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function refresh(RefreshTokenCommand $command): array
    {
        return $this->refreshHandler->handle($command);
    }

    /**
     * Retrieve a single user's profile (domain entity array).
     *
     * @return array<string, mixed>
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getUser(string $userId, string $tenantId): array
    {
        $data = $this->repository->findById($userId);

        if ($data === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "User [{$userId}] not found."
            );
        }

        return $data;
    }

    /**
     * Return a paginated or full list of tenant users.
     *
     * @return array<int, array<string, mixed>>|LengthAwarePaginator
     */
    public function getUsers(GetUsersQuery $query): array|LengthAwarePaginator
    {
        $filters = array_merge($query->filters, ['tenant_id' => $query->tenantId]);

        return $this->repository->findAll(
            filters: $filters,
            sorts: $query->sorts,
            perPage: $query->perPage,
            page: $query->page,
        );
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Publish a domain event to the message broker, silently swallowing errors.
     *
     * @param  array<string, mixed>  $payload
     */
    private function publishEvent(string $topic, array $payload): void
    {
        if ($this->messageBroker === null) {
            return;
        }

        try {
            $this->messageBroker->publish($topic, $payload);
        } catch (\Throwable $e) {
            $this->logger->warning('[AuthService] Failed to publish event', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
