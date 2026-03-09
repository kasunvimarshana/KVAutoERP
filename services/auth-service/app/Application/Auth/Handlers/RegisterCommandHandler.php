<?php

declare(strict_types=1);

namespace App\Application\Auth\Handlers;

use App\Application\Auth\Commands\RegisterCommand;
use App\Domain\Auth\Events\UserRegistered;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\ValueObjects\Password;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;

/**
 * Register Command Handler.
 *
 * Creates a new user record, assigns default roles, and fires the
 * UserRegistered domain event.
 */
final class RegisterCommandHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Handle the register command.
     *
     * @return array<string, mixed>  Newly created user data.
     *
     * @throws \RuntimeException  When the email is already in use within the tenant.
     */
    public function handle(RegisterCommand $command): array
    {
        $existing = $this->userRepository->findByTenantAndEmail(
            $command->tenantId,
            $command->email,
        );

        if ($existing !== null) {
            throw new \RuntimeException(
                'A user with that e-mail address already exists in this tenant.'
            );
        }

        $userId = Uuid::uuid4()->toString();

        $userData = $this->userRepository->create([
            'id'        => $userId,
            'tenant_id' => $command->tenantId,
            'name'      => $command->name,
            'email'     => mb_strtolower(trim($command->email)),
            'password'  => Password::hash($command->password)->getHash(),
            'is_active' => true,
        ]);

        // Assign initial roles.
        foreach ($command->roles as $role) {
            $this->userRepository->assignRole($userId, $role, $command->tenantId);
        }

        // Re-fetch with roles populated.
        $user = $this->userRepository->findByTenantAndEmail($command->tenantId, $command->email);

        Event::dispatch(new UserRegistered(
            userId: $userId,
            tenantId: $command->tenantId,
            email: $command->email,
            name: $command->name,
        ));

        return $user?->toArray() ?? $userData;
    }
}
