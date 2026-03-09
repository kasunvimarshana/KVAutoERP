<?php

declare(strict_types=1);

namespace App\Application\Auth\Handlers;

use App\Application\Auth\Commands\RegisterUserCommand;
use App\Application\Auth\DTOs\UserDTO;
use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Domain\User\Entities\User;
use App\Domain\User\Events\UserRegistered;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

final class RegisterUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TenantRepositoryInterface $tenantRepository,
    ) {}

    /**
     * @throws RuntimeException
     */
    public function handle(RegisterUserCommand $command): UserDTO
    {
        // 1. Validate tenant exists and is active
        $tenant = $this->tenantRepository->findById($command->tenantId);

        if ($tenant === null) {
            throw new RuntimeException("Tenant [{$command->tenantId}] not found.", 404);
        }

        if (! $tenant->isActive()) {
            throw new RuntimeException("Tenant [{$command->tenantId}] is not active.", 403);
        }

        // 2. Validate email value object (throws InvalidArgumentException on invalid format)
        $email = new Email($command->email);

        // 3. Check email uniqueness within tenant
        $existing = $this->userRepository->findByEmail($email->getValue(), $command->tenantId);

        if ($existing !== null) {
            throw new RuntimeException("A user with email [{$email->getValue()}] already exists in this tenant.", 422);
        }

        // 4. Check max_users limit
        $currentCount = $this->userRepository->count(['tenant_id' => $command->tenantId]);

        if ($tenant->max_users > 0 && $currentCount >= $tenant->max_users) {
            throw new RuntimeException('This tenant has reached its maximum user limit.', 422);
        }

        // 5. Create the user inside a transaction
        $password          = Password::fromPlain($command->password);
        $verificationToken = Str::random(64);

        /** @var User $user */
        $user = DB::transaction(function () use ($command, $email, $password, $verificationToken): User {
            /** @var User $user */
            $user = $this->userRepository->create([
                'tenant_id'       => $command->tenantId,
                'organization_id' => $command->organizationId,
                'name'            => $command->name,
                'email'           => $email->getValue(),
                'password'        => $password->getHash(),
                'status'          => User::STATUS_ACTIVE,
                'metadata'        => array_merge(
                    $command->metadata,
                    ['email_verification_token' => $verificationToken]
                ),
            ]);

            // 6. Assign default roles
            if (! empty($command->roles)) {
                $user->assignRole($command->roles);
            }

            return $user;
        });

        // 7. Dispatch domain event (triggers email notification etc.)
        Event::dispatch(new UserRegistered($user, $command->tenantId, $verificationToken));

        Log::info('User registered', [
            'user_id'   => $user->id,
            'tenant_id' => $command->tenantId,
            'email'     => $email->getValue(),
        ]);

        return UserDTO::fromEntity($user);
    }
}
