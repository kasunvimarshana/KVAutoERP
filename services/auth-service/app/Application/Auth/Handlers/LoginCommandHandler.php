<?php

declare(strict_types=1);

namespace App\Application\Auth\Handlers;

use App\Application\Auth\Commands\LoginCommand;
use App\Domain\Auth\Events\UserLoggedIn;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\Services\AuthDomainService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Event;

/**
 * Login Command Handler.
 *
 * Orchestrates the authentication flow: credential validation, token
 * generation, and domain event dispatch.
 */
final class LoginCommandHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly AuthDomainService $authDomainService,
    ) {}

    /**
     * Handle the login command.
     *
     * @return array{access_token: string, refresh_token: string|null, token_type: string, expires_in: int, user: array}
     *
     * @throws AuthenticationException  When credentials are invalid or user not found.
     */
    public function handle(LoginCommand $command): array
    {
        $user = $this->userRepository->findByTenantAndEmail(
            $command->tenantId,
            $command->email,
        );

        if ($user === null) {
            throw new AuthenticationException('These credentials do not match our records.');
        }

        if (!$this->authDomainService->validateCredentials($user, $command->password)) {
            throw new AuthenticationException('These credentials do not match our records.');
        }

        $tokens = $this->authDomainService->generateTokens($user);

        Event::dispatch(new UserLoggedIn(
            userId: $user->getId(),
            tenantId: $user->getTenantId(),
            email: $user->getEmail(),
            ipAddress: $command->deviceInfo['ip_address'] ?? '',
            userAgent: $command->deviceInfo['user_agent'] ?? '',
        ));

        return [
            'access_token'  => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'token_type'    => 'Bearer',
            'expires_in'    => $tokens['expires_in'],
            'user'          => $user->toArray(),
        ];
    }
}
